<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserRole extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => 'user',
                'after' => 'admin',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'role');
    }
}
