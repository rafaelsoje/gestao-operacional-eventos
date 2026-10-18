<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersFromSqlSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Administrador',
                'email' => 'admin@example.com',
                'password' => '$2y$12$jj0ew/fZxGaaadq/BnTpYOMjpYim0yL2msQU3xc2dgcPiMaNUcuvy', // Admin@123456
                'must_change_password' => 0,
                'admin' => 1,
                'role' => 'superuser',
                'avatar' => 'avatar.svg',
                'lastLogin' => '2026-03-12 19:00:00',
                'loginAttempts' => 0,
                'lastAttemptTime' => null,
                'ip' => '127.0.0.1',
                'created_at' => null,
                'updated_at' => '2026-03-12 19:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Operador de Eventos',
                'email' => 'operador@example.com',
                'password' => '$2y$12$OxuKNTvzSvzfvrrVWGwZ2OZDm389LFcHJZp2WXzKUtQFVZeaDcs.u', // Operador@123456
                'must_change_password' => 0,
                'admin' => 1,
                'role' => 'admin',
                'avatar' => 'avatar.svg',
                'lastLogin' => '2026-03-12 10:00:00',
                'loginAttempts' => 0,
                'lastAttemptTime' => null,
                'ip' => '127.0.0.1',
                'created_at' => '2026-02-09 21:00:00',
                'updated_at' => '2026-03-12 10:00:00',
            ],
        ];

        $table = $this->db->table('users');

        foreach ($users as $user) {
            $existingByEmail = $table->where('email', $user['email'])->get()->getRowArray();
            if ($existingByEmail) {
                $id = (int) $existingByEmail['id'];
                $payload = $user;
                unset($payload['id']);
                $table->where('id', $id)->update($payload);
                continue;
            }

            $desiredId = (int) $user['id'];
            $idInUse = $table->where('id', $desiredId)->countAllResults() > 0;
            if ($idInUse) {
                $payload = $user;
                unset($payload['id']);
                $table->insert($payload);
                continue;
            }

            $table->insert($user);
        }
    }
}
