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
                'name' => 'Rafael Soje',
                'email' => 'rafaelsoje@gmail.com',
                'password' => '$2y$10$jBZWZm/Y5xeFKSpoDgE2o.vDxgvYmC0JaLBOUj0CS7FmTUIUEp2nC',
                'must_change_password' => 0,
                'admin' => 1,
                'role' => 'superuser',
                'avatar' => 'avatar.svg',
                'lastLogin' => '2026-03-12 19:03:54',
                'loginAttempts' => 0,
                'lastAttemptTime' => '2026-01-02 22:06:42',
                'ip' => '45.234.215.139',
                'created_at' => null,
                'updated_at' => '2026-03-12 19:03:54',
            ],
            [
                'id' => 6,
                'name' => 'Guilherme Batista',
                'email' => 'operacional@exc.rio.br',
                'password' => '$2y$10$emawTvLnfy80D9Bze1MGb.owRWdg.xo.Dw2BXeFWPty0dpP6kHFm2',
                'must_change_password' => 0,
                'admin' => 1,
                'role' => 'admin',
                'avatar' => 'avatar.svg',
                'lastLogin' => '2026-03-12 10:44:46',
                'loginAttempts' => 0,
                'lastAttemptTime' => '2026-03-12 10:44:36',
                'ip' => '201.76.165.122',
                'created_at' => '2026-02-09 21:40:55',
                'updated_at' => '2026-03-12 10:44:46',
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
