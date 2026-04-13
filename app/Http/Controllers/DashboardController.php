<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
=======
use App\Models\Booking;
use App\Models\Message;
use App\Models\Review;
use App\Services\BookingLifecycleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
>>>>>>> feature2

class DashboardController extends Controller
{
    public function index(BookingLifecycleService $bookingLifecycleService)
    {
        $user = auth()->user();
        abort_if(!$user, 403, 'Not authenticated');

        $bookingLifecycleService->expireStaleBookings(
            Booking::query()->where('user_id', $user->user_id)
        );

<<<<<<< HEAD
        //Count bookings in progress
        $totalBookings = ServiceRequest::where('user_id', $user->user_id)
=======
        $bookings = Booking::query()->where('user_id', $user->user_id);
        $bookingsInProgress = (clone $bookings)
>>>>>>> feature2
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

        $conversationIds = $user->conversations()->pluck('conversation_id');
        $unreadMessagesQuery = Message::query()
            ->whereIn('conversation_id', $conversationIds)
            ->where('sender_type', 'provider')
            ->where('is_read', false);

        $unreadMessages = (clone $unreadMessagesQuery)->count();
        $latestUnreadMessageAt = (clone $unreadMessagesQuery)->max('created_at');
        $averageRating = (float) (Review::query()->where('from_user_id', $user->user_id)->avg('rating') ?? 0);

        $dashboardStats = [
            [
                'label' => 'Bookings in Progress',
                'value' => $bookingsInProgress,
                'href' => route('users.bookings'),
                'icon' => 'fa-calendar-check',
                'icon_class' => 'dashboard-stat-card__icon--bookings',
            ],
            [
                'label' => 'Unread Messages',
                'value' => $unreadMessages,
                'href' => route('user.messages'),
                'icon' => 'fa-comments',
                'icon_class' => 'dashboard-stat-card__icon--messages',
                'badge' => $unreadMessages > 0 ? $unreadMessages . ' unread' : null,
            ],
            [
                'label' => 'Average Rating',
                'value' => number_format($averageRating, 1),
                'href' => route('reviews.reviews'),
                'icon' => 'fa-star',
                'icon_class' => 'dashboard-stat-card__icon--rating',
            ],
        ];

<<<<<<< HEAD
        // Add latest bookings (limit 5)
        $latestBookings = ServiceRequest::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
=======
        $activityFeed = Booking::query()
            ->where('user_id', $user->user_id)
            ->with('service')
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn (Booking $booking) => [
                'text' => 'Booking ' . strtoupper($booking->status_label) . ' for ' . (optional($booking->service)->title ?? 'service'),
                'timestamp' => $booking->updated_at,
                'href' => route('users.bookings'),
                'icon' => 'fa-calendar-day',
                'icon_class' => 'dashboard-activity__icon--booking',
            ]);
>>>>>>> feature2

        if ($unreadMessages > 0) {
            $activityFeed->push([
                'text' => $unreadMessages . ' unread ' . Str::plural('message', $unreadMessages) . ' from providers',
                'timestamp' => $latestUnreadMessageAt ? Carbon::parse($latestUnreadMessageAt) : now(),
                'href' => route('user.messages'),
                'icon' => 'fa-envelope',
                'icon_class' => 'dashboard-activity__icon--message',
            ]);
        }

        $activityFeed = $activityFeed
            ->sortByDesc(fn (array $activity) => $activity['timestamp'])
            ->values();

<<<<<<< HEAD
        foreach ($latestMessages as $message) {
            $activities[] = [
                'type' => 'message',
                'text' => "New message" ,
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
        return view('Users.dashboard', [
            'userDisplay' => $user->full_name ?? 'User',
            'dashboardStats' => $dashboardStats,
            'activityFeed' => $activityFeed,
        ]);
    }
}


>>>>>>> feature2
