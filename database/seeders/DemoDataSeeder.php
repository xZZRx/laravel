<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Institute;
use App\Models\Judge;
use App\Models\JudgeScoreEntry;
use App\Models\Participant;
use App\Models\RankPointConfig;
use App\Models\ResultEntry;
use App\Models\ScoringCriterion;
use App\Models\Season;
use App\Models\User;
use App\Services\ScoreComputationService;
use Illuminate\Database\Seeder;

/**
 * Seeds one worked example of each scoring path so the system is
 * demoable immediately after `php artisan migrate --seed` — a
 * criteria-based/rank-based ARSO event (exercises Level 1 → Level 2 →
 * rank conversion → Level 3) and a by-round/points-based LMC event
 * (exercises the simpler direct-carry-forward path). Both are
 * finalized at the end so leaderboard_snapshots and the Overall Score
 * are populated, not just the raw inputs.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ScoreComputationService::class);
        $institutes = Institute::orderBy('sort_order')->get();
        $organizer = User::where('username', 'organizer')->firstOrFail();
        $tabulator = User::where('username', 'tabulator')->firstOrFail();

        $this->seedArsoEvent($service, $institutes, $organizer, $tabulator);
        $this->seedLmcEvent($service, $institutes, $organizer, $tabulator);
    }

    private function seedArsoEvent(ScoreComputationService $service, $institutes, User $organizer, User $tabulator): void
    {
        $season = Season::program('arso')->active()->firstOrFail();
        $category = EventCategory::program('arso')->where('slug', 'special')->firstOrFail();

        $event = Event::updateOrCreate(
            ['season_id' => $season->id, 'slug' => 'dance-sport-showdown'],
            [
                'event_category_id' => $category->id,
                'name' => 'Dance Sport Showdown',
                'description' => 'Inter-institute production dance competition.',
                'type' => 'special',
                'participation_type' => 'institute',
                'scoring_type' => 'criteria_based',
                'scoring_method' => 'rank_based',
                'status' => 'ongoing',
                'organizer_id' => $organizer->user_id,
                'tabulator_id' => $tabulator->user_id,
                'venue' => 'TCGC Gymnasium',
                'schedule' => now()->addDays(3),
                'leaderboard_visible' => false,
            ]
        );

        // Criteria — max_score IS the weight (criterion-allocated model), sums to 100.
        $criteriaDefs = [
            ['name' => 'Technique & Execution', 'max_score' => 40],
            ['name' => 'Creativity & Choreography', 'max_score' => 30],
            ['name' => 'Stage Presence & Showmanship', 'max_score' => 30],
        ];
        $criteria = collect($criteriaDefs)->map(
            fn ($c, $i) => ScoringCriterion::updateOrCreate(
                ['event_id' => $event->event_id, 'name' => $c['name']],
                ['max_score' => $c['max_score'], 'sort_order' => $i]
            )
        );

        // Rank → points conversion table for this rank_based event.
        foreach ([1 => 10, 2 => 7, 3 => 5, 4 => 3, 5 => 2, 6 => 1] as $rank => $points) {
            RankPointConfig::updateOrCreate(
                ['event_id' => $event->event_id, 'rank_position' => $rank],
                ['points' => $points]
            );
        }

        // Judge panel: 2 registered judges + 1 guest judge (no login).
        $judgeUsers = User::whereIn('username', ['judge1', 'judge2'])->get();
        $judges = $judgeUsers->map(fn ($u, $i) => Judge::updateOrCreate(
            ['event_id' => $event->event_id, 'user_id' => $u->user_id],
            ['name' => $u->name, 'sort_order' => $i]
        ))->values();

        $guestJudge = Judge::updateOrCreate(
            ['event_id' => $event->event_id, 'user_id' => null, 'name' => 'Ms. Elena Guest (external judge)'],
            ['title' => 'Choreographer, Manila Dance Co.', 'sort_order' => 2]
        );
        $judges->push($guestJudge);

        // Participants: all 6 institutes.
        $participants = $institutes->map(fn (Institute $inst) => Participant::updateOrCreate(
            ['event_id' => $event->event_id, 'institute_id' => $inst->id],
            ['name' => $inst->name]
        ));

        // Sample scores: every judge scores every participant on every criterion.
        // Deterministic-but-varied so the ranking is interesting rather than a flat tie.
        $seedBase = 0;
        foreach ($participants as $pIndex => $participant) {
            foreach ($judges as $jIndex => $judge) {
                foreach ($criteria as $criterion) {
                    $seedBase++;
                    $pct = 0.72 + (($seedBase * 37) % 23) / 100; // ~0.72–0.94 of max_score
                    $raw = round(min((float) $criterion->max_score, $criterion->max_score * $pct), 2);

                    JudgeScoreEntry::updateOrCreate(
                        ['judge_id' => $judge->id, 'participant_id' => $participant->participant_id, 'criterion_id' => $criterion->criterion_id],
                        [
                            'event_id' => $event->event_id,
                            'raw_score' => $raw,
                            'weighted_score' => $raw,
                            'submitted_at' => now(),
                        ]
                    );
                }
            }
        }
        Judge::whereIn('id', $judges->pluck('id'))->update(['submitted_at' => now()]);

        $service->finalizeEvent($event->fresh(), $tabulator);
        $service->setLeaderboardVisibility($event->fresh(), true, $tabulator);
    }

    private function seedLmcEvent(ScoreComputationService $service, $institutes, User $organizer, User $tabulator): void
    {
        $season = Season::program('lmc')->active()->firstOrFail();
        $category = EventCategory::program('lmc')->where('slug', 'academic')->firstOrFail();

        $event = Event::updateOrCreate(
            ['season_id' => $season->id, 'slug' => 'quiz-bowl-finals'],
            [
                'event_category_id' => $category->id,
                'name' => 'Quiz Bowl Finals',
                'description' => 'Open general-knowledge quiz bowl, cumulative points across three rounds.',
                'type' => 'academic',
                'participation_type' => 'institute',
                'scoring_type' => 'by_round',
                'scoring_method' => 'points_based',
                'status' => 'ongoing',
                'organizer_id' => $organizer->user_id,
                'tabulator_id' => $tabulator->user_id,
                'venue' => 'TCGC Auditorium',
                'schedule' => now()->addDays(5),
                'leaderboard_visible' => false,
            ]
        );

        $participants = $institutes->map(fn (Institute $inst) => Participant::updateOrCreate(
            ['event_id' => $event->event_id, 'institute_id' => $inst->id],
            ['name' => $inst->name]
        ));

        $scores = [88, 76, 92, 64, 71, 80]; // sample cumulative points, one per institute
        foreach ($participants as $i => $participant) {
            ResultEntry::updateOrCreate(
                ['event_id' => $event->event_id, 'participant_id' => $participant->participant_id],
                [
                    'tabulator_id' => $tabulator->user_id,
                    'overall_score' => $scores[$i] ?? 70,
                    'entered_at' => now(),
                ]
            );
        }

        $service->finalizeEvent($event->fresh(), $tabulator);
        $service->setLeaderboardVisibility($event->fresh(), true, $tabulator);
    }
}
