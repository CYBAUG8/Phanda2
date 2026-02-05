<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
        {
            $now = now();

            $activities = collect([
                [
                    'id' => 1,
                    'type' => 'booking',
                    'text' => 'You booked a cleaning with Alice',
                    'ts' => $now->copy()->subMinutes(2),
                    'read' => false,
                ],
                [
                    'id' => 2,
                    'type' => 'message',
                    'text' => 'Message from Bob: "Can we reschedule?"',
                    'ts' => $now->copy()->subMinutes(12),
                    'read' => false,
                ],
                [
                    'id' => 3,
                    'type' => 'payment',
                    'text' => 'Payment received — R120',
                    'ts' => $now->copy()->subMinutes(90),
                    'read' => true,
                ],
            ])->sortByDesc('ts');

            $messages = collect([
                [
                    'id' => 1,
                    'from' => 'Alice',
                    'text' => 'Hi — are you available tomorrow?',
                    'ts' => $now->copy()->subMinutes(5),
                ],
                [
                    'id' => 2,
                    'from' => 'You',
                    'text' => 'Yes, I have a slot in the morning.',
                    'ts' => $now->copy()->subMinutes(3),
                ],
            ]);

            return view('Users.dashboard', [
                'activities' => $activities,
                'messages'   => $messages,
                'unread'     => $activities->where('read', false)->count(),
                'balance'    => $activities->where('type', 'payment')->count() * 120,
            ]);
        }

}

