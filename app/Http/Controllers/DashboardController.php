<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Not authenticated');
        }

        //Count bookings in progress
        $totalBookings = Booking::where('user_id', $user->user_id)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

        //Count unread messages
        $unreadMessages = Message::whereIn(
                'conversation_id',
                Conversation::where('user_id', $user->user_id)->pluck('conversation_id')//all conversation IDs the user is part of
            )
            ->where('sender_id', '!=', $user->user_id)
            ->where('is_read', false)
            ->count();

        //Average rating (placeholder if you have reviews table)
        $averageRating = 0; // replace with actual query if needed

        // Recent activities
        $activities = [];

        // Add latest bookings (limit 5)
        $latestBookings = Booking::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($latestBookings as $booking) {
            $serviceTitle = $booking->service->title ?? 'Service';
            $activities[] = [
                'type' => 'booking',
                'text' => "Booking for {$serviceTitle}",
                'ts' => $booking->created_at
            ];
        }

        // Add latest unread messages (limit 5)
        $latestMessages = Message::whereIn(
                'conversation_id',
                Conversation::where('user_id', $user->user_id)->pluck('conversation_id')
            )
            ->where('sender_id', '!=', $user->user_id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($latestMessages as $message) {
            $activities[] = [
                'type' => 'message',
                'text' => "New message: " . substr($message->message, 0, 50),
                'ts' => $message->created_at
            ];
        }

        // Sort all activities by timestamp descending
        usort($activities, function ($a, $b) {
            return $b['ts']->timestamp <=> $a['ts']->timestamp;
        });

        return view('users.dashboard', compact(
            'user',
            'totalBookings',
            'unreadMessages',
            'averageRating',
            'activities'
        ));
    }
}