<?php

namespace Database\Seeders;

use App\Models\PublishSchedule;
use Illuminate\Database\Seeder;

class PublishScheduleSeeder extends Seeder
{
    /**
     * Seed the publish schedules with default slots.
     */
    public function run(): void
    {
        $schedules = [
            [
                'time' => '08:00:00',
                'day_of_week' => null,
                'is_active' => true,
                'max_posts' => 1,
            ],
            [
                'time' => '13:00:00',
                'day_of_week' => null,
                'is_active' => true,
                'max_posts' => 1,
            ],
            [
                'time' => '16:00:00',
                'day_of_week' => null,
                'is_active' => true,
                'max_posts' => 1,
            ],
        ];

        foreach ($schedules as $schedule) {
            PublishSchedule::updateOrCreate(
                ['time' => $schedule['time'], 'day_of_week' => $schedule['day_of_week']],
                $schedule
            );
        }
    }
}
