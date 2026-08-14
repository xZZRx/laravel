<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DEMO credentials only — change these before any real deployment.
 * Every account shares one password so it's easy to type during a live
 * defense demo: Luxtab@2026
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Luxtab@2026';

        $accounts = [
            ['username' => 'admin', 'name' => 'System Admin', 'email' => 'admin@luxtab.test', 'role' => Role::Admin],
            ['username' => 'organizer', 'name' => 'Jane Organizer', 'email' => 'organizer@luxtab.test', 'role' => Role::Organizer],
            ['username' => 'tmanager', 'name' => 'Tom Bracketman', 'email' => 'tmanager@luxtab.test', 'role' => Role::TournamentManager],
            ['username' => 'judge1', 'name' => 'Judge Reyes', 'email' => 'judge1@luxtab.test', 'role' => Role::Judge],
            ['username' => 'judge2', 'name' => 'Judge Santos', 'email' => 'judge2@luxtab.test', 'role' => Role::Judge],
            ['username' => 'judge3', 'name' => 'Judge Cruz', 'email' => 'judge3@luxtab.test', 'role' => Role::Judge],
            ['username' => 'tabulator', 'name' => 'Tessa Tabulator', 'email' => 'tabulator@luxtab.test', 'role' => Role::Tabulator],
            ['username' => 'viewer', 'name' => 'Public Viewer', 'email' => 'viewer@luxtab.test', 'role' => Role::Viewer],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['username' => $account['username']],
                [
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'role' => $account['role'],
                    'password' => $password,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
