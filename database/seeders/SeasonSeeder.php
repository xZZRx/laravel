<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) date('Y');

        Season::updateOrCreate(['program' => 'lmc', 'year' => $year], ['label' => "Luxmundis Cup {$year}", 'is_active' => true]);
        Season::updateOrCreate(['program' => 'arso', 'year' => $year], ['label' => "ARSO Festival {$year}", 'is_active' => true]);
    }
}
