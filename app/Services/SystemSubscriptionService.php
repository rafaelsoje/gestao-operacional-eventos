<?php

namespace App\Services;

use App\Models\Settings;
use App\Models\SystemSubscriptionCharge;
use DateTimeImmutable;
use Throwable;

class SystemSubscriptionService
{
    private SystemSubscriptionCharge $charges;

    public function __construct()
    {
        $this->charges = new SystemSubscriptionCharge();
    }

    public function isEnabled(): bool
    {
        return filter_var($this->setting('system_subscription_enabled', env('SYSTEM_SUBSCRIPTION_ENABLED', false)), FILTER_VALIDATE_BOOL);
    }

    public function amount(): float
    {
        return (float)$this->setting('system_subscription_amount', env('SYSTEM_SUBSCRIPTION_AMOUNT', 65));
    }

    public function graceDays(): int
    {
        return max(0, (int)$this->setting('system_subscription_grace_days', env('SYSTEM_SUBSCRIPTION_GRACE_DAYS', 2)));
    }

    public function dueDay(): int
    {
        return min(28, max(1, (int)$this->setting('system_subscription_due_day', env('SYSTEM_SUBSCRIPTION_DUE_DAY', 10))));
    }

    public function warningDay(): int
    {
        $warningDay = min(28, max(1, (int)$this->setting('system_subscription_warning_day', env('SYSTEM_SUBSCRIPTION_WARNING_DAY', 5))));
        return min($warningDay, $this->dueDay());
    }

    public function settings(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'amount' => $this->amount(),
            'due_day' => $this->dueDay(),
            'warning_day' => $this->warningDay(),
            'grace_days' => $this->graceDays(),
        ];
    }

    public function saveSettings(array $data): bool
    {
        if (!$this->settingsTableReady()) {
            return false;
        }

        $settings = new Settings();
        $amount = (float)str_replace(',', '.', str_replace('.', '', (string)($data['amount'] ?? $this->amount())));
        if ($amount <= 0) {
            $amount = 65;
        }

        $payload = [
            'system_subscription_enabled' => !empty($data['enabled']) ? '1' : '0',
            'system_subscription_amount' => number_format($amount, 2, '.', ''),
            'system_subscription_due_day' => (string)min(28, max(1, (int)($data['due_day'] ?? 10))),
            'system_subscription_warning_day' => (string)min(28, max(1, (int)($data['warning_day'] ?? 5))),
            'system_subscription_grace_days' => (string)max(0, (int)($data['grace_days'] ?? 2)),
        ];

        foreach ($payload as $key => $value) {
            if (!$settings->setValue($key, $value)) {
                return false;
            }
        }

        $this->updateEditableOpenCharges($amount, (int)$payload['system_subscription_due_day']);

        return true;
    }

    public function currentCharge(bool $createRemote = false): ?object
    {
        if (!$this->tableReady()) {
            return null;
        }

        $monthRef = date('Y-m');
        $charge = $this->charges->where('month_ref', $monthRef)->first();
        $createdId = null;
        if (!$charge) {
            $id = $this->charges->insert([
                'month_ref' => $monthRef,
                'amount' => $this->amount(),
                'due_date' => $this->dueDateForMonth($monthRef),
                'status' => 'pending',
            ]);
            $createdId = (int)$id;
            $charge = $this->charges->find($id);
        }

        if ($createRemote) {
            $remoteCreated = $this->ensureRemoteCharge($charge);
            if (!$remoteCreated && $createdId !== null) {
                $this->charges->delete($createdId);
                return null;
            }
            $charge = $this->charges->find((int)$charge->id);
        }

        return $charge;
    }

    public function payableCharge(bool $createRemote = false): ?object
    {
        if (!$this->tableReady()) {
            return null;
        }

        $charge = $this->charges
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->first();

        if ($charge && $createRemote) {
            $remoteCreated = $this->ensureRemoteCharge($charge);
            if (!$remoteCreated && $this->isLocalOnlyOpenCharge($charge)) {
                $this->charges->delete((int)$charge->id);
                return null;
            }
            $charge = $this->charges->find((int)$charge->id);
        }

        if (!$charge && $createRemote) {
            $charge = $this->currentCharge(true);
        }

        return $charge;
    }

    public function latestCharges(int $limit = 12): array
    {
        if (!$this->tableReady()) {
            return [];
        }

        return $this->charges
            ->orderBy('month_ref', 'desc')
            ->limit($limit)
            ->findAll();
    }

    public function ensureRemoteCharge(object $charge): bool
    {
        if (!empty($charge->asaas_payment_id) && !$this->isInactiveStatus((string)($charge->status ?? ''))) {
            return true;
        }

        $freshCharge = $this->charges->find((int)$charge->id);
        if ($freshCharge && !empty($freshCharge->asaas_payment_id) && !$this->isInactiveStatus((string)($freshCharge->status ?? ''))) {
            return true;
        }
        if ($freshCharge) {
            $charge = $freshCharge;
        }

        $customerId = trim((string)$this->setting('asaas_customer_id', env('ASAAS_CUSTOMER_ID', '')));
        if ($customerId === '') {
            return false;
        }

        try {
            $asaas = new AsaasService();
            $externalReference = 'system-subscription-' . $charge->month_ref;
            $payment = $asaas->findPaymentByExternalReference($externalReference, $customerId);
            $createdNow = false;

            if ($payment === null) {
                $payment = $asaas->createPixCharge([
                    'customer' => $customerId,
                    'value' => (float)$charge->amount,
                    'dueDate' => (string)$charge->due_date,
                    'description' => 'Mensalidade do sistema - ' . $this->monthLabel((string)$charge->month_ref),
                    'externalReference' => $externalReference,
                ]);
                $createdNow = true;
            }

            if (empty($payment['id'])) {
                return false;
            }

            $payment = $asaas->getPayment((string)$payment['id']);
            $this->updateChargeFromPayment($charge, $payment, $customerId, $createdNow);

            if ($createdNow) {
                $this->notifyTelegram(
                    'Mensalidade do sistema gerada: ' . $this->monthLabel((string)$charge->month_ref)
                    . ' - R$ ' . number_format((float)$charge->amount, 2, ',', '.')
                    . ' - vencimento ' . date('d/m/Y', strtotime((string)$charge->due_date)),
                    TelegramService::EVENT_SUBSCRIPTION_CHARGE_CREATED
                );
            }

            return true;
        } catch (Throwable $e) {
            log_message('error', 'Falha ao gerar cobrança Asaas: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    private function updateChargeFromPayment(object $charge, array $payment, string $customerId, bool $createdNow, ?string $event = null): void
    {
        $qrCode = [];
        if (!empty($payment['id'])) {
            try {
                $qrCode = (new AsaasService())->getPixQrCode((string)$payment['id']);
            } catch (Throwable $e) {
                log_message('error', 'Falha ao obter QR Code Pix Asaas: {message}', ['message' => $e->getMessage()]);
            }
        }

        $status = $this->normalizeAsaasPaymentStatus($payment);
        $inactive = in_array($status, ['cancelled', 'refunded'], true);

        $this->charges->update((int)$charge->id, [
            'asaas_payment_id' => $payment['id'] ?? null,
            'asaas_customer_id' => $customerId,
            'invoice_url' => $inactive ? null : ($payment['invoiceUrl'] ?? null),
            'bank_slip_url' => $inactive ? null : ($payment['bankSlipUrl'] ?? null),
            'pix_qr_code' => $inactive ? null : ($qrCode['encodedImage'] ?? null),
            'pix_payload' => $inactive ? null : ($qrCode['payload'] ?? null),
            'status' => $status,
            'last_webhook_event' => $event ?? ($createdNow ? 'asaas_created' : 'asaas_reused'),
            'last_sync_at' => date('Y-m-d H:i:s'),
            'raw_payload' => json_encode(['payment' => $payment, 'pixQrCode' => $qrCode], JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function syncCharge(object $charge): bool
    {
        if (empty($charge->asaas_payment_id)) {
            return false;
        }

        try {
            $payment = (new AsaasService())->getPayment((string)$charge->asaas_payment_id);
            $this->applyPaymentPayload($payment, 'manual_sync');
            return true;
        } catch (Throwable $e) {
            if (AsaasService::isNotFound($e)) {
                $relinked = $this->relinkMissingRemoteCharge($charge);
                if ($relinked === true) {
                    return true;
                }

                if ($relinked === null) {
                    log_message('error', 'Falha ao procurar cobrança substituta no Asaas: {message}', ['message' => $e->getMessage()]);
                    return false;
                }

                $this->markChargeAsCancelled($charge, 'manual_sync_missing_remote', [
                    'asaas_payment_id' => (string)$charge->asaas_payment_id,
                    'error' => $e->getMessage(),
                ]);
                return true;
            }

            log_message('error', 'Falha ao sincronizar cobrança Asaas: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function syncOpenCharges(): array
    {
        if (!$this->tableReady()) {
            return ['checked' => 0, 'synced' => 0, 'failed' => 0];
        }

        $charges = $this->charges
            ->whereIn('status', ['pending', 'overdue'])
            ->where('asaas_payment_id IS NOT NULL', null, false)
            ->findAll();

        $result = ['checked' => count($charges), 'synced' => 0, 'failed' => 0];
        foreach ($charges as $charge) {
            if ($this->syncCharge($charge)) {
                $result['synced']++;
            } else {
                $result['failed']++;
            }
        }

        return $result;
    }

    public function handleWebhook(array $payload): ?object
    {
        $payment = $payload['payment'] ?? null;
        if (!is_array($payment)) {
            return null;
        }

        return $this->applyPaymentPayload($payment, (string)($payload['event'] ?? 'webhook'), $payload);
    }

    public function subscriptionState(): array
    {
        if (!$this->isEnabled()) {
            return ['enabled' => false, 'blocked' => false, 'warning' => false, 'charge' => null, 'message' => ''];
        }

        $charge = $this->payableCharge(false);
        if (!$charge) {
            return [
                'enabled' => true,
                'blocked' => false,
                'warning' => false,
                'charge' => null,
                'message' => '',
            ];
        }

        if ($this->isPaidStatus((string)$charge->status)) {
            return ['enabled' => true, 'blocked' => false, 'warning' => false, 'charge' => $charge, 'message' => ''];
        }

        $today = new DateTimeImmutable(date('Y-m-d'));
        $due = new DateTimeImmutable((string)$charge->due_date);
        $blockDate = $due->modify('+' . $this->graceDays() . ' days');
        $warningStart = new DateTimeImmutable(substr((string)$charge->due_date, 0, 8) . str_pad((string)$this->warningDay(), 2, '0', STR_PAD_LEFT));
        $isOverdue = $today > $due;
        $blocked = $today > $blockDate;
        $warning = $today >= $warningStart || $isOverdue || $blocked;
        $daysUntilBlock = (int)$today->diff($blockDate)->format('%r%a');
        $daysOverdue = $isOverdue ? (int)$due->diff($today)->format('%a') : 0;
        $daysUntilDue = (int)$today->diff($due)->format('%r%a');

        $message = '';
        if ($blocked) {
            $message = 'Mensalidade vencida. Regularize o pagamento para liberar o sistema.';
        } elseif ($isOverdue) {
            $message = 'Mensalidade vencida. Regularize até ' . $blockDate->format('d/m/Y') . ' para evitar bloqueio.';
        } elseif ($warning) {
            $message = 'Mensalidade pendente. Vencimento em ' . $due->format('d/m/Y') . '.';
        }

        return [
            'enabled' => true,
            'blocked' => $blocked,
            'warning' => $warning,
            'charge' => $charge,
            'message' => $message,
            'due_date' => $due->format('Y-m-d'),
            'block_date' => $blockDate->format('Y-m-d'),
            'warning_start_date' => $warningStart->format('Y-m-d'),
            'days_until_block' => $daysUntilBlock,
            'days_until_due' => $daysUntilDue,
            'days_overdue' => $daysOverdue,
        ];
    }

    public function markCurrentAsPaidManually(): bool
    {
        $charge = $this->payableCharge(false);
        if (!$charge) {
            return false;
        }

        return (bool)$this->charges->update((int)$charge->id, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
            'last_webhook_event' => 'manual_payment',
            'last_sync_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function tableReady(): bool
    {
        return db_connect()->tableExists('system_subscription_charges');
    }

    public function monthLabel(string $monthRef): string
    {
        $parts = explode('-', $monthRef);
        if (count($parts) !== 2) {
            return $monthRef;
        }

        return $parts[1] . '/' . $parts[0];
    }

    public function normalizeAsaasStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH' => 'paid',
            'OVERDUE' => 'overdue',
            'REFUNDED', 'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE' => 'refunded',
            'DELETED', 'CANCELLED', 'REMOVED' => 'cancelled',
            default => 'pending',
        };
    }

    public function normalizeAsaasPaymentStatus(array $payment): string
    {
        if ($this->paymentPayloadIndicatesRemoval($payment)) {
            return 'cancelled';
        }

        return $this->normalizeAsaasStatus((string)($payment['status'] ?? ''));
    }

    private function applyPaymentPayload(array $payment, string $event, ?array $rawWebhook = null): ?object
    {
        $paymentId = (string)($payment['id'] ?? '');
        if ($paymentId === '') {
            return null;
        }

        $charge = $this->charges->where('asaas_payment_id', $paymentId)->first();
        if (!$charge && !empty($payment['externalReference'])) {
            $monthRef = str_replace('system-subscription-', '', (string)$payment['externalReference']);
            $charge = $this->charges->where('month_ref', $monthRef)->first();
        }
        if (!$charge) {
            return null;
        }

        $previousStatus = (string)($charge->status ?? '');
        $status = $this->normalizeAsaasPaymentStatus($payment);
        $paidAt = $status === 'paid'
            ? (($payment['paymentDate'] ?? $payment['clientPaymentDate'] ?? date('Y-m-d')) . ' ' . date('H:i:s'))
            : ($charge->paid_at ?? null);
        $inactive = in_array($status, ['cancelled', 'refunded'], true);

        $this->charges->update((int)$charge->id, [
            'status' => $status,
            'invoice_url' => $inactive ? null : ($payment['invoiceUrl'] ?? ($charge->invoice_url ?? null)),
            'bank_slip_url' => $inactive ? null : ($payment['bankSlipUrl'] ?? ($charge->bank_slip_url ?? null)),
            'pix_qr_code' => $inactive ? null : ($charge->pix_qr_code ?? null),
            'pix_payload' => $inactive ? null : ($charge->pix_payload ?? null),
            'paid_at' => $paidAt,
            'last_webhook_event' => $event,
            'last_sync_at' => date('Y-m-d H:i:s'),
            'raw_payload' => json_encode($rawWebhook ?? ['payment' => $payment], JSON_UNESCAPED_UNICODE),
        ]);

        $updated = $this->charges->find((int)$charge->id);
        if ($updated) {
            $this->notifyStatusChange($updated, $previousStatus, $status);
        }

        return $updated;
    }

    private function relinkMissingRemoteCharge(object $charge): ?bool
    {
        $customerId = trim((string)($charge->asaas_customer_id ?? ''));
        if ($customerId === '') {
            $customerId = trim((string)$this->setting('asaas_customer_id', env('ASAAS_CUSTOMER_ID', '')));
        }

        if ($customerId === '') {
            return false;
        }

        try {
            $asaas = new AsaasService();
            $payment = $asaas->findMatchingPayment(
                $customerId,
                (float)$charge->amount,
                (string)$charge->due_date,
                'system-subscription-' . (string)$charge->month_ref
            );

            if ($payment === null || empty($payment['id'])) {
                return false;
            }

            $payment = $asaas->getPayment((string)$payment['id']);
            $this->updateChargeFromPayment($charge, $payment, $customerId, false, 'manual_sync_relinked_remote');

            return true;
        } catch (Throwable $e) {
            log_message('error', 'Falha ao vincular cobrança substituta no Asaas: {message}', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function paymentPayloadIndicatesRemoval(array $payment): bool
    {
        foreach (['deleted', 'removed', 'cancelled', 'canceled'] as $field) {
            if (array_key_exists($field, $payment) && filter_var($payment[$field], FILTER_VALIDATE_BOOL)) {
                return true;
            }
        }

        foreach (['deletedAt', 'removedAt', 'cancelledAt', 'canceledAt', 'cancellationDate', 'deletedDate'] as $field) {
            if (!empty($payment[$field])) {
                return true;
            }
        }

        return false;
    }

    private function markChargeAsCancelled(object $charge, string $event, array $context = []): ?object
    {
        $previousStatus = (string)($charge->status ?? '');
        $this->charges->update((int)$charge->id, [
            'status' => 'cancelled',
            'invoice_url' => null,
            'bank_slip_url' => null,
            'pix_qr_code' => null,
            'pix_payload' => null,
            'paid_at' => null,
            'last_webhook_event' => $event,
            'last_sync_at' => date('Y-m-d H:i:s'),
            'raw_payload' => json_encode(['remote_missing' => true] + $context, JSON_UNESCAPED_UNICODE),
        ]);

        $updated = $this->charges->find((int)$charge->id);
        if ($updated) {
            $this->notifyStatusChange($updated, $previousStatus, 'cancelled');
        }

        return $updated;
    }

    private function dueDateForMonth(string $monthRef): string
    {
        $dueDay = $this->dueDay();
        return $monthRef . '-' . str_pad((string)$dueDay, 2, '0', STR_PAD_LEFT);
    }

    private function isPaidStatus(string $status): bool
    {
        return $status === 'paid';
    }

    private function isInactiveStatus(string $status): bool
    {
        return in_array($status, ['cancelled', 'refunded'], true);
    }

    private function isLocalOnlyOpenCharge(object $charge): bool
    {
        return in_array((string)($charge->status ?? ''), ['pending', 'overdue'], true)
            && empty($charge->asaas_payment_id)
            && empty($charge->invoice_url)
            && empty($charge->pix_payload)
            && empty($charge->paid_at);
    }

    private function updateEditableOpenCharges(float $amount, int $dueDay): void
    {
        if (!$this->tableReady()) {
            return;
        }

        $openCharges = $this->charges
            ->whereIn('status', ['pending', 'overdue'])
            ->where('asaas_payment_id', null)
            ->findAll();

        foreach ($openCharges as $charge) {
            $monthRef = (string)($charge->month_ref ?? '');
            if ($monthRef === '') {
                continue;
            }

            $this->charges->update((int)$charge->id, [
                'amount' => $amount,
                'due_date' => $monthRef . '-' . str_pad((string)$dueDay, 2, '0', STR_PAD_LEFT),
            ]);
        }
    }

    private function notifyStatusChange(object $charge, string $previousStatus, string $status): void
    {
        if ($previousStatus === $status) {
            return;
        }

        $month = $this->monthLabel((string)($charge->month_ref ?? ''));
        $amount = number_format((float)($charge->amount ?? 0), 2, ',', '.');

        if ($status === 'paid') {
            $this->notifyTelegram(
                'Mensalidade do sistema recebida: ' . $month . ' - R$ ' . $amount,
                TelegramService::EVENT_SUBSCRIPTION_PAYMENT_RECEIVED
            );
            return;
        }

        if ($status === 'overdue') {
            $this->notifyTelegram(
                'Mensalidade do sistema vencida: ' . $month . ' - R$ ' . $amount,
                TelegramService::EVENT_SUBSCRIPTION_OVERDUE
            );
            return;
        }

        if (in_array($status, ['cancelled', 'refunded'], true)) {
            $this->notifyTelegram(
                'Mensalidade do sistema ' . ($status === 'refunded' ? 'estornada' : 'cancelada') . ': ' . $month . ' - R$ ' . $amount,
                TelegramService::EVENT_SUBSCRIPTION_CANCELLED
            );
        }
    }

    private function notifyTelegram(string $message, string $event): void
    {
        (new TelegramService())->send($message, $event);
    }

    private function setting(string $key, mixed $default = null): mixed
    {
        if (!$this->settingsTableReady()) {
            return $default;
        }

        return (new Settings())->getValue($key, $default);
    }

    private function settingsTableReady(): bool
    {
        try {
            return db_connect()->tableExists('settings');
        } catch (Throwable) {
            return false;
        }
    }
}
