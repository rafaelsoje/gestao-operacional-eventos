<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class WebhookSecurityTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private mixed $previous;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previous = $_ENV['ASAAS_WEBHOOK_TOKEN'] ?? null;
        $_ENV['ASAAS_WEBHOOK_TOKEN'] = 'test-token-not-a-production-secret';
    }

    protected function tearDown(): void
    {
        if ($this->previous === null) unset($_ENV['ASAAS_WEBHOOK_TOKEN']);
        else $_ENV['ASAAS_WEBHOOK_TOKEN'] = $this->previous;
        parent::tearDown();
    }

    public function testMissingTokenIsRejectedWithJson(): void
    {
        $this->withBody('{}')->post('/webhooks/asaas')->assertStatus(401);
    }

    public function testUnconfiguredTokenFailsClosed(): void
    {
        $_ENV['ASAAS_WEBHOOK_TOKEN'] = '';
        $this->withBody('{}')->post('/webhooks/asaas')->assertStatus(401);
    }

    public function testInvalidTokenIsRejected(): void
    {
        $this->withHeaders(['asaas-access-token' => 'wrong'])->withBody('{}')
            ->post('/webhooks/asaas')->assertStatus(401);
    }

    public function testAuthenticatedEmptyEventIsAcknowledgedWithoutMatchingPayment(): void
    {
        $result = $this->withHeaders(['asaas-access-token' => 'test-token-not-a-production-secret'])
            ->withBody('{}')->post('/webhooks/asaas');
        $result->assertStatus(200);
        $result->assertJSONFragment(['ok' => true, 'matched' => false]);
    }

    public function testMalformedJsonReturnsBadRequest(): void
    {
        $this->withHeaders(['asaas-access-token' => 'test-token-not-a-production-secret'])
            ->withBody('{')->post('/webhooks/asaas')->assertStatus(400);
    }
}
