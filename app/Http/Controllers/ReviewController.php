<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Services\BookingLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ReviewController extends Controller
{
    /**
     * ================= Web: Show reviews for a provider =================
     */
    public function index(Request $request, BookingLifecycleService $bookingLifecycleService)
    {
        $currentUser = Auth::user();

        $providers = User::where('role', 'provider')->get();

        $selectedProviderId = $request->get('provider');
        $reviewableBooking = null;
        $reviewForBooking = null;
        $bookingContextError = null;

        if ($currentUser && $request->filled('booking')) {
            if (!$this->isBookingReviewSchemaReady()) {
                $bookingContextError = 'Review system update is pending. Please run database migrations.';
            } else {
                $candidateBooking = Booking::with(['service.providerProfile.user'])
                    ->where('id', $request->query('booking'))
                    ->first();

                if ($candidateBooking) {
                    $candidateBooking = $bookingLifecycleService->syncBooking($candidateBooking);
                }

                if (!$candidateBooking || $candidateBooking->user_id !== $currentUser->user_id) {
                    $bookingContextError = 'You can only review your own completed bookings.';
                } elseif ($candidateBooking->status !== 'completed') {
                    $bookingContextError = 'Only completed bookings can be reviewed.';
                } elseif (!$candidateBooking->service || !$candidateBooking->service->providerProfile || !$candidateBooking->service->providerProfile->user) {
                    $bookingContextError = 'Provider details are missing for this booking.';
                } else {
                    $reviewableBooking = $candidateBooking;
                    $selectedProviderId = $candidateBooking->service->providerProfile->user->user_id;
                    $reviewForBooking = Review::where('booking_id', $candidateBooking->id)
                        ->where('from_user_id', $currentUser->user_id)
                        ->first();
                }
            }
        }

        if (!$selectedProviderId) {
            $selectedProviderId = optional($providers->first())->user_id;
        }

        $selectedProvider = $providers->firstWhere('user_id', $selectedProviderId);
        if (!$selectedProvider && $providers->isNotEmpty()) {
            $selectedProvider = $providers->first();
            $selectedProviderId = $selectedProvider->user_id;
        }

        $reviews = Review::with('customer')
            ->orderByDesc('created_at')
            ->when($selectedProviderId, function ($query, $providerId) {
                $query->where('to_user_id', $providerId);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->paginate(5)
            ->withQueryString();

        $allReviews = $selectedProviderId
            ? Review::where('to_user_id', $selectedProviderId)->get()
            : collect();

        $totalReviews = $allReviews->count();
        $averageRating = $totalReviews
            ? round($allReviews->avg('rating'), 1)
            : 0;

        $ratingCounts = collect([5, 4, 3, 2, 1])->map(fn ($s) => [
            'star' => $s,
            'count' => $allReviews->where('rating', $s)->count(),
        ]);

        return view('Users.reviews.reviews', compact(
            'providers',
            'selectedProviderId',
            'selectedProvider',
            'reviews',
            'currentUser',
            'totalReviews',
            'averageRating',
            'ratingCounts',
            'reviewableBooking',
            'reviewForBooking',
            'bookingContextError'
        ));
    }

    /**
     * ================= API: Get reviews for a specific provider =================
     */
    public function apiIndex($provider_id)
    {
        $reviews = Review::where('to_user_id', $provider_id)
            ->with('customer:user_id,full_name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->review_id,
                    'booking_id' => $r->booking_id,
                    'service_id' => $r->service_id,
                    'provider_id' => $r->to_user_id,
                    'user_id' => $r->from_user_id,
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'created_at' => $r->created_at,
                    'updated_at' => $r->updated_at,
                ];
            });

        $averageRating = Review::where('to_user_id', $provider_id)->avg('rating') ?? 0;

        return response()->json([
            'average_rating' => round($averageRating, 1),
            'reviews' => $reviews,
        ], 200);
    }

    /**
     * ================= API: Get reviews for a specific user =================
     */
    public function userReviews($user_id)
    {
        $reviews = Review::where('from_user_id', $user_id)
            ->with('provider:user_id,full_name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'reviews' => $reviews,
        ], 200);
    }

    /**
     * ================= API: Store or update a review =================
     */
    public function store(Request $request, BookingLifecycleService $bookingLifecycleService)
    {
        if (!$this->isBookingReviewSchemaReady()) {
            return $this->invalidReviewStateResponse($request, 'Review system update is pending. Please run database migrations.');
        }

        $validated = $request->validate([
            'booking_id' => 'required|uuid|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $user = Auth::user();
        $booking = Booking::with('service.providerProfile')
            ->where('id', $validated['booking_id'])
            ->firstOrFail();

        $booking = $bookingLifecycleService->syncBooking($booking);

        if ($booking->user_id !== $user->user_id) {
            return $this->forbiddenReviewResponse($request, 'You can only review your own completed bookings.');
        }

        if ($booking->status !== 'completed') {
            return $this->invalidReviewStateResponse($request, 'Only completed bookings can be reviewed.');
        }

        $providerUserId = optional(optional($booking->service)->providerProfile)->user_id;
        if (!$providerUserId) {
            return $this->invalidReviewStateResponse($request, 'Provider details are missing for this booking.');
        }

        $existingReview = Review::where('booking_id', $booking->id)->first();
        if ($existingReview && $existingReview->from_user_id !== $user->user_id) {
            return $this->forbiddenReviewResponse($request, 'You cannot modify this review.');
        }

        if ($existingReview) {
            $existingReview->update([
                'service_id' => $booking->service_id,
                'to_user_id' => $providerUserId,
                'from_user_id' => $user->user_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            $review = $existingReview;
            $statusCode = 200;
        } else {
            $review = Review::create([
                'booking_id' => $booking->id,
                'service_id' => $booking->service_id,
                'to_user_id' => $providerUserId,
                'from_user_id' => $user->user_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            $statusCode = 201;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Review submitted successfully.',
                'review' => [
                    'review_id' => $review->review_id,
                    'booking_id' => $review->booking_id,
                    'service_id' => $review->service_id,
                    'provider_id' => $review->to_user_id,
                    'user_id' => $review->from_user_id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                ],
            ], $statusCode);
        }

        return back()->with('success', 'Review submitted successfully.');
    }

    /**
     * ================= API: Delete a review =================
     */
    public function destroy(Request $request, string $id)
    {
        $review = Review::where('review_id', $id)->firstOrFail();

        if ($review->from_user_id !== Auth::user()->user_id) {
            return $this->forbiddenReviewResponse($request, 'You cannot delete this review.');
        }

        $review->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Review deleted successfully.',
            ], 200);
        }

        return redirect()->back()->with('success', 'Review deleted successfully.');
    }

    private function forbiddenReviewResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return back()->with('error', $message);
    }

    private function invalidReviewStateResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->with('error', $message);
    }

    private function isBookingReviewSchemaReady(): bool
    {
        return Schema::hasColumn('service_reviews', 'booking_id');
    }
}