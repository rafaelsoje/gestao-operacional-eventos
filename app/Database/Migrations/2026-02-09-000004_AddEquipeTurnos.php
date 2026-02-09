<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEquipeTurnos extends Migration
{
    public function up()
    {
        $fields = [
            'equipe_manha' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true, 'default' => 0, 'after' => 'limpeza'],
            'equipe_noite' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true, 'default' => 0, 'after' => 'equipe_manha'],
        ];

        $this->forge->addColumn('operacional', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('operacional', ['equipe_manha', 'equipe_noite']);
    }
}
