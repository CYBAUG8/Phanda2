<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDashboardSummary extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'bookings_requested',
        'bookings_offered',
        'bookings_accepted',
        'bookings_in_progress',
        'unread_messages',
        'average_rating',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'average_rating' => 'decimal:2',
    ];
    public function recentActivities()
    {
        $activities = [];

        if ($this->bookings_requested > 0) {
            $activities[] = [
                'type' => 'booking',
                'text' => "You requested {$this->bookings_requested} bookings",
                'ts' => $this->last_activity_at,
            ];
        }

        if ($this->bookings_offered > 0) {
            $activities[] = [
                'type' => 'booking',
                'text' => "{$this->bookings_offered} booking offers received",
                'ts' => $this->last_activity_at,
            ];
        }

        if ($this->bookings_accepted > 0) {
            $activities[] = [
                'type' => 'booking',
                'text' => "{$this->bookings_accepted} bookings accepted",
                'ts' => $this->last_activity_at,
            ];
        }

        if ($this->bookings_in_progress > 0) {
            $activities[] = [
                'type' => 'booking',
                'text' => "{$this->bookings_in_progress} bookings in progress",
                'ts' => $this->last_activity_at,
            ];
        }

        if ($this->unread_messages > 0) {
            $activities[] = [
                'type' => 'message',
                'text' => "You have {$this->unread_messages} unread messages",
                'ts' => $this->last_activity_at,
            ];
        }

        if ($this->average_rating > 0) {
            $activities[] = [
                'type' => 'rating',
                'text' => "Average rating updated: {$this->average_rating}",
                'ts' => $this->last_activity_at,
            ];
        }

        return collect($activities)->sortByDesc('ts');
    }
}

