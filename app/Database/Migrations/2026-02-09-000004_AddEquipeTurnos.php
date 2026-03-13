<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEquipeTurnos extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('operacional')) {
            return;
        }

        $fields = [];
        if (!$this->db->fieldExists('equipe_manha', 'operacional')) {
            $fields['equipe_manha'] = ['type' => 'TINYINT', 'constraint' => 1, 'null' => true, 'default' => 0, 'after' => 'limpeza'];
        }
        if (!$this->db->fieldExists('equipe_noite', 'operacional')) {
            $fields['equipe_noite'] = ['type' => 'TINYINT', 'constraint' => 1, 'null' => true, 'default' => 0, 'after' => 'equipe_manha'];
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
        foreach (['equipe_manha', 'equipe_noite'] as $field) {
            if ($this->db->fieldExists($field, 'operacional')) {
                $toDrop[] = $field;
            }
        }
        if (!empty($toDrop)) {
            $this->forge->dropColumn('operacional', $toDrop);
        }
    }
}
