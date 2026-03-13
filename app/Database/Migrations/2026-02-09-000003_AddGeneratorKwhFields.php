<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGeneratorKwhFields extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('operacional')) {
            return;
        }

        $fields = [];
        if (!$this->db->fieldExists('leitura_g1', 'operacional')) {
            $fields['leitura_g1'] = ['type' => 'TIME', 'null' => true, 'after' => 'tempo_g1'];
        }
        if (!$this->db->fieldExists('kwh_entrada_g1', 'operacional')) {
            $fields['kwh_entrada_g1'] = ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'after' => 'leitura_g1'];
        }
        if (!$this->db->fieldExists('kwh_saida_g1', 'operacional')) {
            $fields['kwh_saida_g1'] = ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'after' => 'kwh_entrada_g1'];
        }
        if (!$this->db->fieldExists('leitura_g2', 'operacional')) {
            $fields['leitura_g2'] = ['type' => 'TIME', 'null' => true, 'after' => 'tempo_g2'];
        }
        if (!$this->db->fieldExists('kwh_entrada_g2', 'operacional')) {
            $fields['kwh_entrada_g2'] = ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'after' => 'leitura_g2'];
        }
        if (!$this->db->fieldExists('kwh_saida_g2', 'operacional')) {
            $fields['kwh_saida_g2'] = ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'after' => 'kwh_entrada_g2'];
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
        foreach (['leitura_g1', 'kwh_entrada_g1', 'kwh_saida_g1', 'leitura_g2', 'kwh_entrada_g2', 'kwh_saida_g2'] as $field) {
            if ($this->db->fieldExists($field, 'operacional')) {
                $toDrop[] = $field;
            }
        }
        if (!empty($toDrop)) {
            $this->forge->dropColumn('operacional', $toDrop);
        }
    }
}
