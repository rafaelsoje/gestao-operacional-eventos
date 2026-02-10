<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsersMustChangePassword extends Migration
{
    public function up()
    {
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

    public function down()
    {
        $this->forge->dropColumn('users', 'must_change_password');
    }
}
