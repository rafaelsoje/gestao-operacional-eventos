<?php

use App\Models\SystemSubscriptionCharge;
use App\Services\SystemSubscriptionService;
use CodeIgniter\Test\CIUnitTestCase;

final class SubscriptionPaymentFlowTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $db = db_connect();
        $this->assertSame('SQLite3', $db->DBDriver, 'Payment tests require isolated SQLite.');
        $forge = \Config\Database::forge();
        $forge->dropTable('system_subscription_charges', true);
        $forge->dropTable('settings', true);
        $forge->addField(['id' => ['type' => 'INTEGER', 'auto_increment' => true]]);
        foreach (['month_ref', 'amount', 'due_date', 'status', 'asaas_payment_id', 'asaas_customer_id',
                  'invoice_url', 'bank_slip_url', 'pix_qr_code', 'pix_payload', 'paid_at',
                  'last_webhook_event', 'last_sync_at', 'raw_payload', 'created_at', 'updated_at'] as $name) {
            $forge->addField([$name => ['type' => 'TEXT', 'null' => true]]);
        }
        $forge->addKey('id', true)->createTable('system_subscription_charges');
        $forge->addField(['key' => ['type' => 'TEXT'], 'value' => ['type' => 'TEXT'], 'updated_at' => ['type' => 'TEXT', 'null' => true]]);
        $forge->addKey('key', true)->createTable('settings');
        $db->table('settings')->insertBatch([
            ['key' => 'telegram_enabled', 'value' => 'false'],
            ['key' => 'system_subscription_enabled', 'value' => 'true'],
            ['key' => 'system_subscription_grace_days', 'value' => '5'],
        ]);
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge();
        $forge->dropTable('system_subscription_charges', true);
        $forge->dropTable('settings', true);
        parent::tearDown();
    }

    public function testReceivedPaymentUnblocksAndDuplicateKeepsPaidState(): void
    {
        (new SystemSubscriptionCharge())->insert([
            'month_ref' => date('Y-m'), 'amount' => 65,
            'due_date' => date('Y-m-d', strtotime('-6 days')), 'status' => 'overdue',
            'asaas_payment_id' => 'pay_test_isolated',
        ]);
        $service = new SystemSubscriptionService();
        $this->assertTrue($service->subscriptionState()['blocked']);
        $payload = ['event' => 'PAYMENT_RECEIVED', 'payment' => ['id' => 'pay_test_isolated', 'status' => 'RECEIVED']];
        $this->assertSame('paid', $service->handleWebhook($payload)->status);
        $this->assertFalse($service->subscriptionState()['blocked']);
        $this->assertSame('paid', $service->handleWebhook($payload)->status);
        $this->assertSame(1, (new SystemSubscriptionCharge())->countAllResults());
    }

    public function testConfirmedPaymentAlsoUnblocks(): void
    {
        (new SystemSubscriptionCharge())->insert([
            'month_ref' => date('Y-m'), 'amount' => 65,
            'due_date' => date('Y-m-d', strtotime('-6 days')), 'status' => 'overdue',
            'asaas_payment_id' => 'pay_test_confirmed',
        ]);
        $service = new SystemSubscriptionService();
        $this->assertSame('paid', $service->handleWebhook([
            'event' => 'PAYMENT_CONFIRMED', 'payment' => ['id' => 'pay_test_confirmed', 'status' => 'CONFIRMED'],
        ])->status);
        $this->assertFalse($service->subscriptionState()['blocked']);
    }

    public function testFiveGraceDaysDoNotBlockUntilFollowingDay(): void
    {
        (new SystemSubscriptionCharge())->insert([
            'month_ref' => date('Y-m'), 'amount' => 65,
            'due_date' => date('Y-m-d', strtotime('-5 days')), 'status' => 'overdue',
        ]);
        $this->assertFalse((new SystemSubscriptionService())->subscriptionState()['blocked']);
    }
}
