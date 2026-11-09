<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterGeneratorReadingColumnsToVarchar extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('operacional')) {
            return;
        }

        $fields = [];
        if ($this->db->fieldExists('leitura_g1', 'operacional')) {
            $fields['leitura_g1'] = [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'null' => true,
            ];
        }
        if ($this->db->fieldExists('leitura_g2', 'operacional')) {
            $fields['leitura_g2'] = [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'null' => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->modifyColumn('operacional', $fields);
        }
    }

    public function down(): void
    {
        if (!$this->db->tableExists('operacional')) {
            return;
        }

        $fields = [];
        if ($this->db->fieldExists('leitura_g1', 'operacional')) {
            $fields['leitura_g1'] = [
                'type' => 'TIME',
                'null' => true,
            ];
        }
        if ($this->db->fieldExists('leitura_g2', 'operacional')) {
            $fields['leitura_g2'] = [
                'type' => 'TIME',
                'null' => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->modifyColumn('operacional', $fields);
        }
    }
}
