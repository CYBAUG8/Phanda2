<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\User;
use App\Models\Booking;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
=======
use App\Models\Booking;
use App\Models\Message;
use App\Models\Review;
>>>>>>> services-bookings-feature

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_if(!$user, 403, 'Not authenticated');

        $bookings = Booking::where('user_id', $user->user_id);
        $bookingsInProgress = (clone $bookings)->whereIn('status', ['pending', 'confirmed', 'in_progress'])->count();

<<<<<<< HEAD
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
=======
        $conversationIds = $user->conversations()->pluck('conversation_id');
        $unreadMessages = Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_type', 'provider')
            ->where('is_read', false)
            ->count();

        $averageRating = Review::where('from_user_id', $user->user_id)->avg('rating') ?? 0;

        $summary = (object) [
            'name' => $user->full_name,
            'bookings_in_progress' => $bookingsInProgress,
            'unread_messages' => $unreadMessages,
            'average_rating' => $averageRating,
        ];

        $activities = collect();

        $activities = $activities
            ->merge(
                Booking::where('user_id', $user->user_id)
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($booking) => [
                        'type' => 'booking',
                        'text' => 'Booking ' . strtoupper($booking->status) . ' for ' . optional($booking->service)->title,
                        'ts' => $booking->updated_at,
                    ])
            )
            ->sortByDesc('ts')
            ->values();

        return view('users.dashboard', compact('summary', 'activities'));
    }
}
>>>>>>> services-bookings-feature
