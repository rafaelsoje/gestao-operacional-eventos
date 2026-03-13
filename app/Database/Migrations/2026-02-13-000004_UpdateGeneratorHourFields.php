<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateGeneratorHourFields extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('operacional')) {
            return;
        }

        $fields = [];
        if (!$this->db->fieldExists('leitura_saida_g1', 'operacional')) {
            $fields['leitura_saida_g1'] = ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true, 'after' => 'leitura_g1'];
        }
        if (!$this->db->fieldExists('leitura_saida_g2', 'operacional')) {
            $fields['leitura_saida_g2'] = ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true, 'after' => 'leitura_g2'];
        }
        if (!empty($fields)) {
            $this->forge->addColumn('operacional', $fields);
        }
    }

    public function down(): void
    {
        if (!$this->db->tableExists('operacional')) {
            return;
        }

        $toDrop = [];
        foreach (['leitura_saida_g1', 'leitura_saida_g2'] as $field) {
            if ($this->db->fieldExists($field, 'operacional')) {
                $toDrop[] = $field;
            }
        }
        if (!empty($toDrop)) {
            $this->forge->dropColumn('operacional', $toDrop);
        }
    }
}
