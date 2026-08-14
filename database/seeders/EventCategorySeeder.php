<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

/**
 * ARSO's category weights are seeded exactly as Ch.1.1/Ch.4.4.1 state
 * them: Academic 30%, Special 50%, Socio-Cultural 20%.
 *
 * LMC's categories (Academic/Special/Sports) are real — the previous
 * build already used them — but the thesis never gives LMC-specific
 * category *weights*, only ARSO's. The 33.33/33.33/33.34 split below is
 * a neutral placeholder so Σweights = 100 and the Level 3 formula is
 * well-defined; edit these from Settings (or here) once SSC confirms
 * LMC's real weighting, or leave them equal if LMC is meant to weight
 * every category the same.
 */
class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $arso = [
            ['name' => 'Academic', 'slug' => 'academic', 'weight_pct' => 30.00, 'icon' => 'graduation-cap'],
            ['name' => 'Special', 'slug' => 'special', 'weight_pct' => 50.00, 'icon' => 'star'],
            ['name' => 'Socio-Cultural', 'slug' => 'socio-cultural', 'weight_pct' => 20.00, 'icon' => 'users'],
        ];

        $lmc = [
            ['name' => 'Academic', 'slug' => 'academic', 'weight_pct' => 33.33, 'icon' => 'graduation-cap'],
            ['name' => 'Special', 'slug' => 'special', 'weight_pct' => 33.33, 'icon' => 'star'],
            ['name' => 'Sports', 'slug' => 'sports', 'weight_pct' => 33.34, 'icon' => 'trophy'],
        ];

        foreach (['arso' => $arso, 'lmc' => $lmc] as $program => $categories) {
            foreach ($categories as $i => $category) {
                EventCategory::updateOrCreate(
                    ['program' => $program, 'slug' => $category['slug']],
                    [...$category, 'program' => $program, 'sort_order' => $i]
                );
            }
        }
    }
}
