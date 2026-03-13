<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNoiseMonitoringFields extends Migration
{
    public function up(): void
    {
        $fields = [];

        if (!$this->db->fieldExists('ruido_tribuna_c', 'operacional')) {
            $fields['ruido_tribuna_c'] = [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'null' => true,
                'after' => 'monitor_sonoro',
            ];
        }

        if (!$this->db->fieldExists('ruido_pista_cavalo', 'operacional')) {
            $fields['ruido_pista_cavalo'] = [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'null' => true,
                'after' => 'ruido_tribuna_c',
            ];
        }

        if (!$this->db->fieldExists('ruido_corredor_jb', 'operacional')) {
            $fields['ruido_corredor_jb'] = [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'null' => true,
                'after' => 'ruido_pista_cavalo',
            ];
        }

        if (!$this->db->fieldExists('ruido_media', 'operacional')) {
            $fields['ruido_media'] = [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'null' => true,
                'after' => 'ruido_corredor_jb',
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('operacional', $fields);
        }
    }

    public function down(): void
    {
        $toDrop = [];
        foreach (['ruido_tribuna_c', 'ruido_pista_cavalo', 'ruido_corredor_jb', 'ruido_media'] as $field) {
            if ($this->db->fieldExists($field, 'operacional')) {
                $toDrop[] = $field;
            }
        }

        if (!empty($toDrop)) {
            $this->forge->dropColumn('operacional', $toDrop);
        }
    }
}
