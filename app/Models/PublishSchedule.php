<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublishSchedule extends Model
{
    use HasFactory;

    protected $table = 'publish_schedules';

    protected $fillable = [
        'time',
        'day_of_week',
        'is_active',
        'max_posts',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
        'max_posts' => 'integer',
    ];

    /**
     * Get human-readable day name for the schedule.
     */
    public function getDayNameAttribute(): string
    {
        if ($this->day_of_week === null) {
            return 'Setiap hari';
        }

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return $days[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Get the time formatted for display (e.g., "08:00").
     */
    public function getFormattedTimeAttribute(): string
    {
        return substr($this->time, 0, 5);
    }
}
