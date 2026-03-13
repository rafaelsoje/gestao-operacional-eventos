<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTaxaCozinha extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('operacional', 'taxa_cozinha');
    }

    public function down(): void
    {
        $this->forge->addColumn('operacional', [
            'taxa_cozinha' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
        ]);
    }
}
