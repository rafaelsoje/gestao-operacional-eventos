<?php

use App\Services\AsaasService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AsaasServiceTest extends CIUnitTestCase
{
    public function testDetectsMissingRemotePaymentByStatusCode(): void
    {
        $this->assertTrue(AsaasService::isNotFound(new RuntimeException('Cobrança não encontrada.', 404)));
    }

    public function testDetectsMissingRemotePaymentByMessage(): void
    {
        $this->assertTrue(AsaasService::isNotFound(new RuntimeException('Pagamento inexistente no Asaas.')));
    }

    public function testMatchesActivePaymentWithSameCustomerValueAndDueDate(): void
    {
        $this->assertTrue(AsaasService::paymentMatchesCharge([
            'customer' => 'cus_123',
            'value' => 65.00,
            'dueDate' => '2026-06-10',
            'status' => 'PENDING',
        ], 65.00, '2026-06-10', 'cus_123'));
    }

    public function testDoesNotMatchCancelledReplacementPayment(): void
    {
        $this->assertFalse(AsaasService::paymentMatchesCharge([
            'customer' => 'cus_123',
            'value' => 65.00,
            'dueDate' => '2026-06-10',
            'status' => 'CANCELLED',
        ], 65.00, '2026-06-10', 'cus_123'));
    }

    public function testDoesNotMatchDifferentDueDate(): void
    {
        $this->assertFalse(AsaasService::paymentMatchesCharge([
            'customer' => 'cus_123',
            'value' => 65.00,
            'dueDate' => '2026-06-11',
            'status' => 'PENDING',
        ], 65.00, '2026-06-10', 'cus_123'));
    }
}
