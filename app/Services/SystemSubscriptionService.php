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
        return (float)$this->setting('system_subscription_amount', env('SYSTEM_SUBSCRIPTION_AMOUNT', 60));
    }

    public function graceDays(): int
    {
        return max(0, (int)$this->setting('system_subscription_grace_days', env('SYSTEM_SUBSCRIPTION_GRACE_DAYS', 5)));
    }

    public function dueDay(): int
    {
        return min(28, max(1, (int)$this->setting('system_subscription_due_day', env('SYSTEM_SUBSCRIPTION_DUE_DAY', 10))));
    }

    public function settings(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'amount' => $this->amount(),
            'due_day' => $this->dueDay(),
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
            $amount = 60;
        }

        $payload = [
            'system_subscription_enabled' => !empty($data['enabled']) ? '1' : '0',
            'system_subscription_amount' => number_format($amount, 2, '.', ''),
            'system_subscription_due_day' => (string)min(28, max(1, (int)($data['due_day'] ?? 10))),
            'system_subscription_grace_days' => (string)max(0, (int)($data['grace_days'] ?? 5)),
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
        if (!$charge) {
            $id = $this->charges->insert([
                'month_ref' => $monthRef,
                'amount' => $this->amount(),
                'due_date' => $this->dueDateForMonth($monthRef),
                'status' => 'pending',
            ]);
            $charge = $this->charges->find($id);
        }

        if ($createRemote) {
            $this->ensureRemoteCharge($charge);
            $charge = $this->charges->find((int)$charge->id);
        }

        return $charge;
    }

    public function payableCharge(bool $createRemote = false): ?object
    {
        if (!$this->tableReady()) {
            return null;
        }

        $this->currentCharge(false);

        $charge = $this->charges
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->first();

        if (!$charge) {
            $charge = $this->currentCharge(false);
        }

        if ($charge && $createRemote) {
            $this->ensureRemoteCharge($charge);
            $charge = $this->charges->find((int)$charge->id);
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
        if (!empty($charge->asaas_payment_id)) {
            return true;
        }

        $customerId = trim((string)$this->setting('asaas_customer_id', env('ASAAS_CUSTOMER_ID', '')));
        if ($customerId === '') {
            return false;
        }

        try {
            $asaas = new AsaasService();
            $payment = $asaas->createPixCharge([
                'customer' => $customerId,
                'value' => (float)$charge->amount,
                'dueDate' => (string)$charge->due_date,
                'description' => 'Mensalidade do sistema - ' . $this->monthLabel((string)$charge->month_ref),
                'externalReference' => 'system-subscription-' . $charge->month_ref,
            ]);

            $qrCode = [];
            if (!empty($payment['id'])) {
                $qrCode = $asaas->getPixQrCode((string)$payment['id']);
            }

            $this->charges->update((int)$charge->id, [
                'asaas_payment_id' => $payment['id'] ?? null,
                'asaas_customer_id' => $customerId,
                'invoice_url' => $payment['invoiceUrl'] ?? null,
                'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
                'pix_qr_code' => $qrCode['encodedImage'] ?? null,
                'pix_payload' => $qrCode['payload'] ?? null,
                'status' => $this->normalizeAsaasStatus((string)($payment['status'] ?? 'PENDING')),
                'last_sync_at' => date('Y-m-d H:i:s'),
                'raw_payload' => json_encode(['payment' => $payment, 'pixQrCode' => $qrCode], JSON_UNESCAPED_UNICODE),
            ]);

            return true;
        } catch (Throwable $e) {
            log_message('error', 'Falha ao gerar cobrança Asaas: {message}', ['message' => $e->getMessage()]);
            return false;
        }
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
            log_message('error', 'Falha ao sincronizar cobrança Asaas: {message}', ['message' => $e->getMessage()]);
            return false;
        }
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
                'warning' => true,
                'charge' => null,
                'message' => 'Execute as migrations para ativar o controle de assinatura.',
            ];
        }

        if ($this->isPaidStatus((string)$charge->status)) {
            return ['enabled' => true, 'blocked' => false, 'warning' => false, 'charge' => $charge, 'message' => ''];
        }

        $today = new DateTimeImmutable(date('Y-m-d'));
        $due = new DateTimeImmutable((string)$charge->due_date);
        $blockDate = $due->modify('+' . $this->graceDays() . ' days');
        $isOverdue = $today > $due;
        $blocked = $today > $blockDate;
        $daysUntilBlock = (int)$today->diff($blockDate)->format('%r%a');
        $daysOverdue = $isOverdue ? (int)$due->diff($today)->format('%a') : 0;

        $message = $blocked
            ? 'Mensalidade vencida. Regularize o pagamento para liberar o sistema.'
            : 'Mensalidade pendente. Regularize até ' . $blockDate->format('d/m/Y') . ' para evitar bloqueio.';

        return [
            'enabled' => true,
            'blocked' => $blocked,
            'warning' => $isOverdue || !$this->isPaidStatus((string)$charge->status),
            'charge' => $charge,
            'message' => $message,
            'due_date' => $due->format('Y-m-d'),
            'block_date' => $blockDate->format('Y-m-d'),
            'days_until_block' => $daysUntilBlock,
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
            'DELETED', 'CANCELLED' => 'cancelled',
            default => 'pending',
        };
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

        $status = $this->normalizeAsaasStatus((string)($payment['status'] ?? ''));
        $paidAt = $status === 'paid'
            ? (($payment['paymentDate'] ?? $payment['clientPaymentDate'] ?? date('Y-m-d')) . ' ' . date('H:i:s'))
            : ($charge->paid_at ?? null);

        $this->charges->update((int)$charge->id, [
            'status' => $status,
            'invoice_url' => $payment['invoiceUrl'] ?? ($charge->invoice_url ?? null),
            'bank_slip_url' => $payment['bankSlipUrl'] ?? ($charge->bank_slip_url ?? null),
            'paid_at' => $paidAt,
            'last_webhook_event' => $event,
            'last_sync_at' => date('Y-m-d H:i:s'),
            'raw_payload' => json_encode($rawWebhook ?? ['payment' => $payment], JSON_UNESCAPED_UNICODE),
        ]);

        return $this->charges->find((int)$charge->id);
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
