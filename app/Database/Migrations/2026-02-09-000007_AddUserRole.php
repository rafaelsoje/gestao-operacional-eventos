<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserRole extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('users') && !$this->db->fieldExists('role', 'users')) {
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
    }

    public function down(): void
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('role', 'users')) {
            $this->forge->dropColumn('users', 'role');
        }
    }
}
