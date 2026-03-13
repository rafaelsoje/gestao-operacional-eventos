<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGeneratorUsageAndMaintenance extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('operacional')) {
            $fields = [];
            if (!$this->db->fieldExists('uso_min_g1', 'operacional')) {
                $fields['uso_min_g1'] = ['type' => 'INT', 'null' => true, 'after' => 'leitura_saida_g1'];
            }
            if (!$this->db->fieldExists('uso_min_g2', 'operacional')) {
                $fields['uso_min_g2'] = ['type' => 'INT', 'null' => true, 'after' => 'leitura_saida_g2'];
            }
            if (!empty($fields)) {
                $this->forge->addColumn('operacional', $fields);
            }
        }

        if (!$this->db->tableExists('gerador_manutencoes')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'gerador' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 2,
                ],
                'data_manutencao' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'observacao' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey(['gerador', 'data_manutencao']);
            $this->forge->createTable('gerador_manutencoes');
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('operacional')) {
            $toDrop = [];
            foreach (['uso_min_g1', 'uso_min_g2'] as $field) {
                if ($this->db->fieldExists($field, 'operacional')) {
                    $toDrop[] = $field;
                }
            }
            if (!empty($toDrop)) {
                $this->forge->dropColumn('operacional', $toDrop);
            }
        }
        if ($this->db->tableExists('gerador_manutencoes')) {
            $this->forge->dropTable('gerador_manutencoes', true);
        }
    }
}
