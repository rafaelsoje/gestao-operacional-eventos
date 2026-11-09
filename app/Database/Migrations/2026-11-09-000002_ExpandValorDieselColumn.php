<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExpandValorDieselColumn extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('operacional')) {
            return;
        }

        if ($this->db->fieldExists('valor_diesel', 'operacional')) {
            $this->forge->modifyColumn('operacional', [
                'valor_diesel' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if (!$this->db->tableExists('operacional')) {
            return;
        }

        if ($this->db->fieldExists('valor_diesel', 'operacional')) {
            $this->forge->modifyColumn('operacional', [
                'valor_diesel' => [
                    'type' => 'DECIMAL',
                    'constraint' => '3,2',
                    'null' => true,
                ],
            ]);
        }
    }
}
