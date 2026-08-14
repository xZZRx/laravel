<?php

namespace Database\Seeders;

use App\Models\Institute;
use Illuminate\Database\Seeder;

/**
 * PLACEHOLDER DATA. The thesis refers to "TCGC's six institutes" but
 * doesn't name them in the text I had access to — replace `code`/`name`
 * below with the real six before your defense/demo. Everything else
 * (colors, the rest of the seeded data) works with whatever six rows
 * exist here.
 */
class InstituteSeeder extends Seeder
{
    public function run(): void
    {
        $institutes = [
            ['code' => 'INST-A', 'name' => 'Institute A (replace with real name)', 'color_hex' => '#1c46f5'],
            ['code' => 'INST-B', 'name' => 'Institute B (replace with real name)', 'color_hex' => '#e0245e'],
            ['code' => 'INST-C', 'name' => 'Institute C (replace with real name)', 'color_hex' => '#17bf63'],
            ['code' => 'INST-D', 'name' => 'Institute D (replace with real name)', 'color_hex' => '#f5a623'],
            ['code' => 'INST-E', 'name' => 'Institute E (replace with real name)', 'color_hex' => '#9013fe'],
            ['code' => 'INST-F', 'name' => 'Institute F (replace with real name)', 'color_hex' => '#00b8d9'],
        ];

        foreach ($institutes as $i => $institute) {
            Institute::updateOrCreate(
                ['code' => $institute['code']],
                [...$institute, 'is_active' => true, 'sort_order' => $i]
            );
        }
    }
}
