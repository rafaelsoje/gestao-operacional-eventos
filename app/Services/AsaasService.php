<?php

namespace App\Services;

use RuntimeException;

class AsaasService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string)env('ASAAS_BASE_URL', 'https://api.asaas.com/v3'), '/');
        $this->apiKey = trim((string)env('ASAAS_API_KEY', ''));
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function createPixCharge(array $data): array
    {
        return $this->request('POST', '/payments', [
            'customer' => $data['customer'],
            'billingType' => 'PIX',
            'value' => (float)$data['value'],
            'dueDate' => $data['dueDate'],
            'description' => $data['description'] ?? 'Mensalidade do sistema',
            'externalReference' => $data['externalReference'] ?? null,
        ]);
    }

    public function getPixQrCode(string $paymentId): array
    {
        return $this->request('GET', '/payments/' . rawurlencode($paymentId) . '/pixQrCode');
    }

    public function getPayment(string $paymentId): array
    {
        return $this->request('GET', '/payments/' . rawurlencode($paymentId));
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('ASAAS_API_KEY não configurada.');
        }

        $ch = curl_init($this->baseUrl . $path);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Audiovisual/1.0 (+https://audiovisual.rafaelsoje.com)',
            'access_token: ' . $this->apiKey,
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Audiovisual/1.0 (+https://audiovisual.rafaelsoje.com)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException('Falha ao conectar no Asaas: ' . $error);
        }

        $decoded = json_decode((string)$body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Resposta inválida do Asaas.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $decoded['errors'][0]['description'] ?? $decoded['message'] ?? 'Erro ao chamar API do Asaas.';
            throw new RuntimeException((string)$message);
        }

        return $decoded;
    }
}
