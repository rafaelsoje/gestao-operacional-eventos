<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGeneratorKwhFields extends Migration
{
    public function up()
    {
        $fields = [
            'leitura_g1' => ['type' => 'TIME', 'null' => true, 'after' => 'tempo_g1'],
            'kwh_entrada_g1' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'after' => 'leitura_g1'],
            'kwh_saida_g1' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'after' => 'kwh_entrada_g1'],
            'leitura_g2' => ['type' => 'TIME', 'null' => true, 'after' => 'tempo_g2'],
            'kwh_entrada_g2' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'after' => 'leitura_g2'],
            'kwh_saida_g2' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'after' => 'kwh_entrada_g2'],
        ];

        $this->forge->addColumn('operacional', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('operacional', [
            'leitura_g1',
            'kwh_entrada_g1',
            'kwh_saida_g1',
            'leitura_g2',
            'kwh_entrada_g2',
            'kwh_saida_g2',
        ]);
    }
}
