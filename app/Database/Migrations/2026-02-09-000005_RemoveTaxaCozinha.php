<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTaxaCozinha extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('operacional') && $this->db->fieldExists('taxa_cozinha', 'operacional')) {
            $this->forge->dropColumn('operacional', 'taxa_cozinha');
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('operacional') && !$this->db->fieldExists('taxa_cozinha', 'operacional')) {
            $this->forge->addColumn('operacional', [
                'taxa_cozinha' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            ]);
        }
    }
}
