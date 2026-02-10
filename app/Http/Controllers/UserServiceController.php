<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;

class UserServiceController extends Controller
{
    /**
     * Display the services listing with search, category filtering, and sorting.
     */
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = Service::with('category')->where('is_active', true);

        // Search by title or description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('provider_name', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($category = $request->input('category')) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        // Filter by location
        if ($location = $request->input('location')) {
            $query->where('location', 'like', "%{$location}%");
        }

        // Sorting
        switch ($request->input('sort', 'rating')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'rating':
            default:
                $query->orderBy('rating', 'desc');
                break;
        }

        $services = $query->paginate(12)->withQueryString();

        $filters = [
            'search'   => $request->input('search', ''),
            'category' => $request->input('category', ''),
            'location' => $request->input('location', ''),
            'sort'     => $request->input('sort', 'rating'),
        ];

        return view('users.services', compact('services', 'categories', 'filters'));
    }
}
