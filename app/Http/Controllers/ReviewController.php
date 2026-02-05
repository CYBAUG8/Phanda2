<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class ReviewController extends Controller
{
    /**
     * ================= Web: Show reviews for a provider =================
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        // Defined available providers (could later come from DB)
        $providers = [
            ['id' => 'cleaner-001', 'name' => 'General Cleaning'],
            ['id' => 'plumber-001', 'name' => 'Plumbing Repair'],
            ['id' => 'garden-001', 'name' => 'Garden Maintenance'],
        ];

        $selectedProviderId = $request->get('provider', $providers[0]['id']);

        // Fetch reviews with user info
        $reviews = Review::with('user')
        ->where('provider_id', $selectedProviderId)
        ->orderByDesc('created_at')
        ->paginate(2) 
        ->withQueryString(); 


        // The current user's review for this provider (if any)
        $userReviewForSelected = $currentUser
            ? $reviews->firstWhere('user_id', $currentUser->id)
            : null;

        return view('Users.reviews.reviews', compact(
            'providers',
            'selectedProviderId',
            'reviews',
            'currentUser',
            'userReviewForSelected'
        ));
    }


    /**
     * ================= API: Get reviews for a specific provider =================
     */
    public function apiIndex($provider_id)
    {
        $reviews = Review::where('provider_id', $provider_id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'service_id' => $r->service_id,
                    'provider_id' => $r->provider_id,
                    'user_id' => $r->user_id,
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'created_at' => $r->created_at,
                    'updated_at' => $r->updated_at,
                ];
            });

        $averageRating = Review::where('provider_id', $provider_id)->avg('rating') ?? 0;

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
        $reviews = Review::where('user_id', $user_id)
            ->with('provider:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'reviews' => $reviews
        ], 200);
    }

    /**
     * ================= API: Store or update a review =================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'  => 'required|string',
            'user_id'     => 'required|string',
            'provider_id' => 'required|string',
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'required|string',
        ]);

        $review = Review::updateOrCreate(
            [
                'user_id'     => $validated['user_id'],
                'provider_id' => $validated['provider_id'],
            ],
            [
                'id'         => (string) Str::uuid(),
                'service_id' => $validated['service_id'],
                'rating'     => $validated['rating'],
                'comment'    => $validated['comment'],
            ]
        );

        $review->load('user');

        return response()->json([
            'message' => 'Review submitted successfully.',
            'review' => [
                'id'         => $review->id,
                'service_id' => $review->service_id,
                'provider_id'=> $review->provider_id,
                'user_id'    => $review->user_id,
                'rating'     => $review->rating,
                'comment'    => $review->comment,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
            ],
        ], 201);
    }

    /**
     * ================= API: Delete a review =================
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully'], 200);
    }
}
