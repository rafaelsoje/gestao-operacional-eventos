<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemSubscriptionCharges extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('system_subscription_charges')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'month_ref' => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => false],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => false, 'default' => 0.00],
            'due_date' => ['type' => 'DATE', 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false, 'default' => 'pending'],
            'asaas_payment_id' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'asaas_customer_id' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'invoice_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'bank_slip_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'pix_qr_code' => ['type' => 'MEDIUMTEXT', 'null' => true],
            'pix_payload' => ['type' => 'MEDIUMTEXT', 'null' => true],
            'paid_at' => ['type' => 'DATETIME', 'null' => true],
            'last_webhook_event' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'last_sync_at' => ['type' => 'DATETIME', 'null' => true],
            'raw_payload' => ['type' => 'MEDIUMTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('month_ref');
        $this->forge->addKey('status');
        $this->forge->addKey('asaas_payment_id');
        $this->forge->createTable('system_subscription_charges', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('system_subscription_charges', true);
    }
}
