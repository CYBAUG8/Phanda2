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

        // Get providers from DB
        $providers = User::where('role', 'provider')->get();

        $selectedProviderId = $request->get('provider')
            ?? optional($providers->first())->user_id;

        $selectedProvider = $providers
            ->firstWhere('user_id', $selectedProviderId);

        // Fetch reviews for provider
        $reviews = Review::with('customer')
            ->where('to_user_id', $selectedProviderId)
            ->orderByDesc('created_at')
            ->paginate(5);

        $allReviews = Review::where('to_user_id', $selectedProviderId)->get();

        $totalReviews = $allReviews->count();
        $averageRating = $totalReviews
            ? round($allReviews->avg('rating'), 1)
            : 0;

        $ratingCounts = collect([5,4,3,2,1])->map(fn ($s) => [
            'star' => $s,
            'count' => $allReviews->where('rating', $s)->count(),
        ]);

        $userReviewForSelected = $currentUser
            ? $allReviews->firstWhere('from_user_id', $currentUser->user_id)
            : null;

        return view('Users.reviews.reviews', compact(
            'providers',
            'selectedProviderId',
            'selectedProvider',
            'reviews',
            'currentUser',
            'totalReviews',
            'averageRating',
            'ratingCounts',
            'userReviewForSelected'
        ));
    }


    /**
     * ================= API: Get reviews for a specific provider =================
     */
    public function apiIndex($provider_id)
    {
        $reviews = Review::where('to_user_id', $provider_id)
            ->with('customer:id,full_name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
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
            ->with('provider:id,full_name')
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
        $request->validate([
            'service_id' => 'required|string',
            'provider_id' => 'required|uuid|exists:users,user_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        Review::updateOrCreate(
            [
                'from_user_id' => Auth::user()->user_id,
                'to_user_id'   => $request->provider_id,
            ],
            [
                'service_id' => $request->service_id,
                'rating'     => $request->rating,
                'comment'    => $request->comment,
            ]
        );

        return back()->with('success', 'Review submitted successfully.');
    }


    /**
     * ================= API: Delete a review =================
     */
    public function destroy($id)
    {
        Review::where('review_id', $id)->delete();

        return redirect()->back()->with('success', 'Review deleted successfully.');
    }

}
