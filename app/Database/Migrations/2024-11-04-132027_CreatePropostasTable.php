<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePropostasTable extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('propostas')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'excolab_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'event_name' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'client_name' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'value' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => false,
                'default' => 0.00,
            ],
            'charge' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
                'default' => 0.00,
            ],
            'rent' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
                'default' => 0.00,
            ],
            'third' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
                'default' => 0.00,
            ],
            'status' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'rider' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'saldo' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('propostas', true);
        $this->db->query('CREATE INDEX idx_propostas_start_date ON propostas (start_date)');
        $this->db->query('CREATE INDEX idx_propostas_status_start_date ON propostas (status, start_date)');
        $this->db->query('CREATE INDEX idx_propostas_created_at ON propostas (created_at)');
    }

    public function down(): void
    {
        $this->forge->dropTable('propostas', true);
    }
}
