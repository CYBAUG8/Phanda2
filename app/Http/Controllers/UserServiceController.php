<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class UserServiceController extends Controller
{
    public function index(Request $request)
    {
        $user         = $request->user();
        $userSettings = $user?->settings;
        $userGender   = $user?->userProfile?->gender;

<<<<<<< HEAD
        $categories  = Category::orderBy('name')->get();
        $radiusKm    = max(1, min((int) $request->input('radius_km', 100), 100));
=======
        $radiusKm = max(1, min((int) $request->input('radius_km', 25), 100));
>>>>>>> feature2
        $coordinates = $this->resolveUserCoordinates($request);

        $query = Service::query()
            ->with(['category', 'providerProfile'])
            ->withCount(['reviews as live_reviews_count'])
            ->withAvg('reviews as live_rating', 'rating')
            ->where('is_active', true);

        // ── same_gender_provider ──────────────────────────────────────
        if ($userSettings?->same_gender_provider && $userGender) {
            $query->whereHas('providerProfile.user.userProfile', function ($q) use ($userGender) {
                $q->where('gender', $userGender);
            });
        }

        // ── repeat_providers ──────────────────────────────────────────
        if ($userSettings?->repeat_providers) {
            $usedProviderIds = ServiceRequest::where('user_id', $user->user_id)
                ->where('status', 'completed')
                ->pluck('provider_id');

            if ($usedProviderIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('services.provider_id', $usedProviderIds);
            }
        }

        // ── search ────────────────────────────────────────────────────
        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('provider_name', 'like', "%{$search}%");
            });
        }

<<<<<<< HEAD
        // ── category ──────────────────────────────────────────────────
=======
>>>>>>> feature2
        if ($category = $request->input('category')) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

<<<<<<< HEAD
        // ── location search ───────────────────────────────────────────
        $location          = trim((string) $request->input('location', ''));
=======
        $location = trim((string) $request->input('location', ''));
>>>>>>> feature2
        $hasLocationSearch = $location !== '';

        if ($hasLocationSearch) {
            $terms = preg_split('/[\s,]+/', mb_strtolower($location), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $terms = array_values(array_filter(array_slice($terms, 0, 5), function ($term) {
                return mb_strlen($term) >= 2;
            }));

            $query->where(function ($q) use ($location, $terms) {
                $full = mb_strtolower($location);
                $q->whereRaw('LOWER(services.location) like ?', ['%' . $full . '%']);
                foreach ($terms as $term) {
                    $q->orWhereRaw('LOWER(services.location) like ?', ['%' . $term . '%']);
                }
            });
        }

        // ── proximity ─────────────────────────────────────────────────
        $showProximityWarning = false;

        if ($hasLocationSearch) {
            $query->select('services.*');
        } elseif ($coordinates !== null) {
            $lat         = $coordinates['lat'];
            $lng         = $coordinates['lng'];
            $distanceSql = $this->distanceSql('pp.last_lat', 'pp.last_lng');
            $bindings    = [$lat, $lng, $lat];

            $query->join('provider_profiles as pp', 'pp.provider_id', '=', 'services.provider_id')
                ->whereNotNull('pp.last_lat')
                ->whereNotNull('pp.last_lng')
                ->select('services.*')
                ->selectRaw("{$distanceSql} as distance_km", $bindings)
                ->whereRaw("{$distanceSql} <= ?", array_merge($bindings, [$radiusKm]))
                ->where(function ($q) use ($distanceSql, $bindings) {
                    $q->whereNull('pp.service_radius_km')
                        ->orWhere('pp.service_radius_km', '<=', 0)
                        ->orWhereRaw("{$distanceSql} <= pp.service_radius_km", $bindings);
                });
        } else {
            $showProximityWarning = true;
            $query->whereRaw('1 = 0');
        }

        // ── sorting ───────────────────────────────────────────────────
        $sort = $request->input('sort');
        if (!$sort) {
            $sort = (!$hasLocationSearch && $coordinates) ? 'nearest' : 'rating';
        }

        switch ($sort) {
            case 'nearest':
                if (!$hasLocationSearch && $coordinates !== null) {
                    $query->orderBy('distance_km', 'asc');
                } else {
                    $query->orderBy('rating', 'desc');
                }
                break;
<<<<<<< HEAD
            case 'price_asc':  $query->orderBy('base_price', 'asc');           break;
            case 'price_desc': $query->orderBy('base_price', 'desc');          break;
            case 'newest':     $query->orderBy('services.created_at', 'desc'); break;
=======
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'newest':
                $query->orderBy('services.created_at', 'desc');
                break;
>>>>>>> feature2
            case 'rating':
            default:           $query->orderBy('rating', 'desc');              break;
        }

        $services = $query->paginate(12)->withQueryString();

        $filters = [
<<<<<<< HEAD
            'search'    => (string) $request->input('search', ''),
            'category'  => (string) $request->input('category', ''),
            'location'  => (string) $request->input('location', ''),
            'sort'      => (string) $sort,
=======
            'search' => (string) $request->input('search', ''),
            'category' => (string) $request->input('category', ''),
            'location' => (string) $request->input('location', ''),
            'sort' => (string) $sort,
>>>>>>> feature2
            'radius_km' => $radiusKm,
            'lat'       => $coordinates['lat'] ?? null,
            'lng'       => $coordinates['lng'] ?? null,
        ];

        return view('Users.services', compact('services', 'categories', 'filters', 'showProximityWarning'));
    }

    public function locationSuggestions(Request $request): \Illuminate\Http\JsonResponse
    {
        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $locations = Service::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(location) LIKE ?', ['%' . mb_strtolower($term) . '%'])
            ->select('location')
            ->distinct()
            ->orderByRaw('LOWER(location)')
            ->limit(10)
            ->pluck('location');

        return response()->json($locations);
    }

    private function resolveUserCoordinates(Request $request): ?array
    {
        $requestLat = $request->input('lat');
        $requestLng = $request->input('lng');

        if (is_numeric($requestLat) && is_numeric($requestLng)) {
            $lat = (float) $requestLat;
            $lng = (float) $requestLng;
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                return ['lat' => $lat, 'lng' => $lng];
            }
        }

        $address = Address::query()
            ->where('user_id', $request->user()->user_id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->first();

        return $address === null ? null : [
            'lat' => (float) $address->latitude,
            'lng' => (float) $address->longitude,
        ];
    }

    private function distanceSql(string $latColumn, string $lngColumn): string
    {
        return "(6371 * acos(least(1, greatest(-1, cos(radians(?)) * cos(radians({$latColumn})) * cos(radians({$lngColumn}) - radians(?)) + sin(radians(?)) * sin(radians({$latColumn})) ))))";
    }
}
