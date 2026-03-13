<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateGeneratorHourFields extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('operacional', [
            'leitura_g1' => ['name' => 'leitura_g1', 'type' => 'VARCHAR', 'constraint' => 7, 'null' => true],
            'leitura_g2' => ['name' => 'leitura_g2', 'type' => 'VARCHAR', 'constraint' => 7, 'null' => true],
        ]);

        $this->forge->addColumn('operacional', [
            'leitura_saida_g1' => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true, 'after' => 'leitura_g1'],
            'leitura_saida_g2' => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true, 'after' => 'leitura_g2'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('operacional', ['leitura_saida_g1', 'leitura_saida_g2']);

        $this->forge->modifyColumn('operacional', [
            'leitura_g1' => ['name' => 'leitura_g1', 'type' => 'TIME', 'null' => true],
            'leitura_g2' => ['name' => 'leitura_g2', 'type' => 'TIME', 'null' => true],
        ]);
    }
}
