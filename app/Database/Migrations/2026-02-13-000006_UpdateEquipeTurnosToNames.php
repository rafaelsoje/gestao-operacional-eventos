<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateEquipeTurnosToNames extends Migration
{
    public function up(): void
    {
        if ($this->db->fieldExists('equipe_manha', 'operacional')) {
            $this->forge->modifyColumn('operacional', [
                'equipe_manha' => [
                    'name' => 'equipe_manha',
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                    'default' => null,
                ],
            ]);
        }

        if ($this->db->fieldExists('equipe_noite', 'operacional')) {
            $this->forge->modifyColumn('operacional', [
                'equipe_noite' => [
                    'name' => 'equipe_noite',
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                    'default' => null,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('equipe_manha', 'operacional')) {
            $this->forge->modifyColumn('operacional', [
                'equipe_manha' => [
                    'name' => 'equipe_manha',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => true,
                    'default' => 0,
                ],
            ]);
        }

        if ($this->db->fieldExists('equipe_noite', 'operacional')) {
            $this->forge->modifyColumn('operacional', [
                'equipe_noite' => [
                    'name' => 'equipe_noite',
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => true,
                    'default' => 0,
                ],
            ]);
        }
    }
}
