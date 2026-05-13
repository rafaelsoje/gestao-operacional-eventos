<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaxasCozinha extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('taxas_cozinha')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'operacional_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'cliente' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'responsavel_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'valor_taxa' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'embutida_contrato' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'houve_negociacao' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'detalhes_negociacao' => ['type' => 'TEXT', 'null' => true],
            'status_financeiro' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'pendente'],
            'forma_pagamento' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'valor_pago' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'data_pagamento' => ['type' => 'DATE', 'null' => true],
            'comprovante' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'cobranca_realizada' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'data_cobranca' => ['type' => 'DATE', 'null' => true],
            'observacoes' => ['type' => 'TEXT', 'null' => true],
            'responsavel_pagamento' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'prevista_contrato' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('operacional_id');
        $this->forge->addKey('status_financeiro');
        $this->forge->createTable('taxas_cozinha', true);
    }

    public function down(): void
    {
        if ($this->db->tableExists('taxas_cozinha')) {
            $this->forge->dropTable('taxas_cozinha', true);
        }
    }
}
