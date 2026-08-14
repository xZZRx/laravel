<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InstituteSeeder::class,
            EventCategorySeeder::class,
            SeasonSeeder::class,
            UserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
