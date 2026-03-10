<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;

class UserServiceController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $radiusKm = max(1, min((int) $request->input('radius_km', 25), 100));
        $coordinates = $this->resolveUserCoordinates($request);

        $query = Service::query()
            ->with(['category', 'providerProfile'])
            ->where('is_active', true);

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('provider_name', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        $location = trim((string) $request->input('location', ''));
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

        $showProximityWarning = false;

        if ($hasLocationSearch) {
            // Location search should return services in the searched area,
            // regardless of current GPS coordinates.
            $query->select('services.*');
        } elseif ($coordinates !== null) {
            $lat = $coordinates['lat'];
            $lng = $coordinates['lng'];
            $distanceSql = $this->distanceSql('pp.last_lat', 'pp.last_lng');
            $bindings = [$lat, $lng, $lat];

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
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'newest':
                $query->orderBy('services.created_at', 'desc');
                break;
            case 'rating':
            default:
                $query->orderBy('rating', 'desc');
                break;
        }

        $services = $query->paginate(12)->withQueryString();

        $filters = [
            'search' => (string) $request->input('search', ''),
            'category' => (string) $request->input('category', ''),
            'location' => (string) $request->input('location', ''),
            'sort' => (string) $sort,
            'radius_km' => $radiusKm,
            'lat' => $coordinates['lat'] ?? null,
            'lng' => $coordinates['lng'] ?? null,
        ];

        return view('Users.services', compact('services', 'categories', 'filters', 'showProximityWarning'));
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

        if ($address === null) {
            return null;
        }

        return [
            'lat' => (float) $address->latitude,
            'lng' => (float) $address->longitude,
        ];
    }

    private function distanceSql(string $latColumn, string $lngColumn): string
    {
        return "(6371 * acos(least(1, greatest(-1, cos(radians(?)) * cos(radians({$latColumn})) * cos(radians({$lngColumn}) - radians(?)) + sin(radians(?)) * sin(radians({$latColumn})) ))))";
    }
}