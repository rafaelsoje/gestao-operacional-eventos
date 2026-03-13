<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsersMustChangePassword extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('users') && !$this->db->fieldExists('must_change_password', 'users')) {
            $this->forge->addColumn('users', [
                'must_change_password' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => false,
                    'default' => 0,
                    'after' => 'password',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('must_change_password', 'users')) {
            $this->forge->dropColumn('users', 'must_change_password');
        }
    }
}
