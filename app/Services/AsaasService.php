<?php

namespace App\Services;

use RuntimeException;
use Throwable;

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

    public function findPaymentByExternalReference(string $externalReference, ?string $customerId = null): ?array
    {
        $query = [
            'externalReference' => $externalReference,
            'limit' => 100,
        ];

        if ($customerId !== null && $customerId !== '') {
            $query['customer'] = $customerId;
        }

        $response = $this->request('GET', '/payments?' . http_build_query($query));
        $payments = $response['data'] ?? [];
        if (!is_array($payments)) {
            return null;
        }

        foreach ($payments as $payment) {
            if (!is_array($payment)) {
                continue;
            }

            if (($payment['externalReference'] ?? '') !== $externalReference) {
                continue;
            }

            $status = strtoupper((string)($payment['status'] ?? ''));
            if (in_array($status, ['DELETED', 'CANCELLED', 'REFUNDED'], true)) {
                continue;
            }

            return $payment;
        }

        return null;
    }

    public function findMatchingPayment(string $customerId, float $value, string $dueDate, ?string $preferredExternalReference = null): ?array
    {
        $fallback = null;
        $limit = 100;

        for ($offset = 0; $offset <= 500; $offset += $limit) {
            $response = $this->request('GET', '/payments?' . http_build_query([
                'customer' => $customerId,
                'limit' => $limit,
                'offset' => $offset,
            ]));
            $payments = $response['data'] ?? [];
            if (!is_array($payments) || $payments === []) {
                return $fallback;
            }

            foreach ($payments as $payment) {
                if (!is_array($payment) || !self::paymentMatchesCharge($payment, $value, $dueDate, $customerId)) {
                    continue;
                }

                if ($preferredExternalReference !== null && ($payment['externalReference'] ?? null) === $preferredExternalReference) {
                    return $payment;
                }

                $fallback ??= $payment;
            }

            if (empty($response['hasMore']) || count($payments) < $limit) {
                return $fallback;
            }
        }

        return $fallback;
    }

    public function getPixQrCode(string $paymentId): array
    {
        return $this->request('GET', '/payments/' . rawurlencode($paymentId) . '/pixQrCode');
    }

    public function getPayment(string $paymentId): array
    {
        return $this->request('GET', '/payments/' . rawurlencode($paymentId));
    }

    public static function isNotFound(Throwable $e): bool
    {
        if ((int)$e->getCode() === 404) {
            return true;
        }

        $message = mb_strtolower($e->getMessage());
        return str_contains($message, 'not found')
            || str_contains($message, 'não encontrada')
            || str_contains($message, 'nao encontrada')
            || str_contains($message, 'não encontrado')
            || str_contains($message, 'nao encontrado')
            || str_contains($message, 'inexistente');
    }

    public static function paymentMatchesCharge(array $payment, float $value, string $dueDate, ?string $customerId = null): bool
    {
        $status = strtoupper((string)($payment['status'] ?? ''));
        if (in_array($status, ['DELETED', 'CANCELLED', 'REFUNDED'], true)) {
            return false;
        }

        if ($customerId !== null && $customerId !== '') {
            $paymentCustomer = $payment['customer'] ?? '';
            if (is_array($paymentCustomer)) {
                $paymentCustomer = $paymentCustomer['id'] ?? '';
            }

            if ((string)$paymentCustomer !== $customerId) {
                return false;
            }
        }

        $paymentDueDate = (string)($payment['dueDate'] ?? $payment['originalDueDate'] ?? '');
        if ($paymentDueDate !== $dueDate) {
            return false;
        }

        return abs(((float)($payment['value'] ?? 0)) - $value) < 0.01;
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
            throw new RuntimeException((string)$message, $statusCode);
        }

        return $decoded;
    }
}
