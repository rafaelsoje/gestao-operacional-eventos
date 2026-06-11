<?php

namespace App\Controllers;

use App\Models\Logs;
use App\Services\SystemSubscriptionService;
use CodeIgniter\HTTP\ResponseInterface;

class SubscriptionController extends Home
{
    public function index(): string|ResponseInterface
    {
        $service = new SystemSubscriptionService();
        $charge = $service->payableCharge(true);
        if ($charge && !in_array((string)($charge->status ?? ''), ['pending', 'overdue'], true)) {
            $charge = null;
        }

        return view('assinatura', [
            'subscriptionPage' => true,
            'enabled' => $service->isEnabled(),
            'tableReady' => $service->tableReady(),
            'charge' => $charge,
            'charges' => $service->latestCharges(),
            'state' => $service->subscriptionState(),
            'settings' => $service->settings(),
            'asaasConfigured' => trim((string)env('ASAAS_API_KEY', '')) !== '' && trim((string)env('ASAAS_CUSTOMER_ID', '')) !== '',
            'canManualRelease' => $this->isSuperuser(),
        ]);
    }

    public function sync(): ResponseInterface
    {
        $service = new SystemSubscriptionService();
        $charge = $service->payableCharge(false);
        if (!$charge) {
            return redirect()->route('subscription.index')->with('error', 'Cobrança atual não encontrada.');
        }

        if (!$service->syncCharge($charge)) {
            return redirect()->route('subscription.index')->with('error', 'Não foi possível sincronizar a cobrança no Asaas.');
        }

        return redirect()->route('subscription.index')->with('success', 'Cobrança sincronizada com sucesso.');
    }

    public function regeneratePix(): ResponseInterface
    {
        $service = new SystemSubscriptionService();
        $charge = $service->payableCharge(true);
        if (!$charge || empty($charge->asaas_payment_id)) {
            return redirect()->route('subscription.index')->with('error', 'Não foi possível gerar a cobrança no Asaas. Verifique ASAAS_API_KEY e ASAAS_CUSTOMER_ID.');
        }

        return redirect()->route('subscription.index')->with('success', 'Cobrança Pix disponível.');
    }

    public function manualPaid(): ResponseInterface
    {
        if (!$this->isSuperuser()) {
            return redirect()->route('subscription.index')->with('error', 'Apenas o superusuário pode liberar manualmente.');
        }

        $service = new SystemSubscriptionService();
        if (!$service->markCurrentAsPaidManually()) {
            return redirect()->route('subscription.index')->with('error', 'Não foi possível liberar a mensalidade.');
        }

        $user = session()->get('user');
        (new Logs())->insert([
            'title' => 'ASSINATURA LIBERADA MANUALMENTE',
            'description' => ($user->name ?? 'Superusuário') . ' liberou manualmente a mensalidade em ' . date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('subscription.index')->with('success', 'Mensalidade liberada manualmente.');
    }

    public function settingsUpdate(): ResponseInterface
    {
        if (!$this->isSuperuser()) {
            return redirect()->route('subscription.index')->with('error', 'Apenas o superusuário pode alterar a assinatura.');
        }

        $post = $this->request->getPost();
        $amount = (string)($post['amount'] ?? '');
        $dueDay = (int)($post['due_day'] ?? 0);
        $warningDay = (int)($post['warning_day'] ?? 0);
        $graceDays = (int)($post['grace_days'] ?? 0);

        if (trim($amount) === '' || $dueDay < 1 || $dueDay > 28 || $warningDay < 1 || $warningDay > 28 || $warningDay > $dueDay || $graceDays < 0 || $graceDays > 60) {
            return redirect()->route('subscription.index')->with('error', 'Configuração inválida. Use alerta e vencimento entre 1 e 28, alerta até o vencimento e tolerância entre 0 e 60 dias.');
        }

        $service = new SystemSubscriptionService();
        $saved = $service->saveSettings([
            'enabled' => $this->request->getPost('enabled') === '1',
            'amount' => $amount,
            'due_day' => $dueDay,
            'warning_day' => $warningDay,
            'grace_days' => $graceDays,
        ]);

        if (!$saved) {
            return redirect()->route('subscription.index')->with('error', 'Não foi possível salvar as configurações.');
        }

        $user = session()->get('user');
        (new Logs())->insert([
            'title' => 'CONFIGURAÇÃO DE ASSINATURA',
            'description' => ($user->name ?? 'Superusuário') . ' atualizou configurações de assinatura em ' . date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('subscription.index')->with('success', 'Configurações da assinatura salvas com sucesso.');
    }

    public function webhook(): ResponseInterface
    {
        if (!$this->isValidWebhookToken()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'unauthorized']);
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = json_decode((string)$this->request->getBody(), true);
        }
        if (!is_array($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'invalid_payload']);
        }

        $charge = (new SystemSubscriptionService())->handleWebhook($payload);
        if (!$charge) {
            return $this->response->setJSON(['ok' => true, 'matched' => false]);
        }

        return $this->response->setJSON(['ok' => true, 'matched' => true]);
    }

    private function isValidWebhookToken(): bool
    {
        $expected = trim((string)env('ASAAS_WEBHOOK_TOKEN', ''));
        if ($expected === '') {
            return true;
        }

        $received = trim($this->request->getHeaderLine('asaas-access-token'));
        if ($received === '') {
            $received = trim($this->request->getHeaderLine('Asaas-Access-Token'));
        }
        if ($received === '') {
            $received = trim((string)$this->request->getGet('token'));
        }

        return $received !== '' && hash_equals($expected, $received);
    }

    private function isSuperuser(): bool
    {
        $user = session()->get('user');
        $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', ''));
        return $user && !empty($superEmail) && ($user->email ?? '') === $superEmail;
    }
}
