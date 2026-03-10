<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Message;
use App\Models\Review;
use App\Services\BookingLifecycleService;

class DashboardController extends Controller
{
    public function index(BookingLifecycleService $bookingLifecycleService)
    {
        $user = auth()->user();
        abort_if(!$user, 403, 'Not authenticated');

        $bookingLifecycleService->expireStaleBookings(
            Booking::query()->where('user_id', $user->user_id)
        );

        $bookings = Booking::where('user_id', $user->user_id);
        $bookingsInProgress = (clone $bookings)->whereIn('status', ['pending', 'confirmed', 'in_progress'])->count();

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
                    ->with('service')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($booking) => [
                        'type' => 'booking',
                        'text' => 'Booking ' . strtoupper($booking->status_label) . ' for ' . optional($booking->service)->title,
                        'ts' => $booking->updated_at,
                    ])
            )
            ->sortByDesc('ts')
            ->values();

        return view('users.dashboard', compact('summary', 'activities'));
    }
}