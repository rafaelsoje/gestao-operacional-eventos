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
}
