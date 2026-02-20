<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserDashboardSummary;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // DEV AUTO-LOGIN (LOCAL ONLY)
        if (app()->environment('local') && !auth()->check()) {
            $user = User::first();

            if ($user) {
                auth()->login($user);
            }
        }

        $user = auth()->user();

        if (!$user) {
            abort(403, 'Not authenticated');
        }

        $summary = UserDashboardSummary::where('user_id', $user->user_id)->firstOrFail();

        $activities = [
            ['type' => 'booking', 'text' => 'Booked cleaning', 'ts' => now()->subMinutes(10)],
            ['type' => 'message', 'text' => 'Message from Alice', 'ts' => now()->subMinutes(5)],
            ['type' => 'payment', 'text' => 'Payment received R120', 'ts' => now()->subHours(1)],
        ];
        $summary = UserDashboardSummary::where('user_id', $user->user_id)->firstOrFail();
            $activities = $summary->recentActivities();

            return view('users.dashboard', compact('summary', 'activities'));
        }
}

