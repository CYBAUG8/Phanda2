<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;

class LoginHistoryController extends Controller
{
    // Get login history
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = $user->loginHistories()->orderBy('login_at', 'desc');
            
            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }
            
            // Limit results if needed
            $limit = $request->input('limit', 50);
            $loginHistory = $query->limit($limit)->get();
            
            // Format for frontend
            $formattedHistory = $loginHistory->map(function ($history) {
                return [
                    'date' => $history->login_at->format('Y-m-d H:i'),
                    'device' => $history->device ?: $this->parseUserAgent($history->user_agent),
                    'location' => $history->location ?: 'Unknown',
                    'status' => ucfirst($history->status),
                    'ip_address' => $history->ip_address,
                ];
            });

            return response()->json([
                'success' => true,
                'login_history' => $formattedHistory
            ]);
        } catch (\Exception $e) {
            Log::error('Get login history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch login history'
            ], 500);
        }
    }

    // Record new login
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            
            // Get location from IP
            $location = 'Unknown';
            if ($ipAddress && $ipAddress !== '127.0.0.1') {
                try {
                    $position = Location::get($ipAddress);
                    if ($position) {
                        $location = $position->cityName ?: $position->countryName;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to get location from IP: ' . $e->getMessage());
                }
            }
            
            // Parse device from user agent
            $device = $this->parseUserAgent($userAgent);
            
            // Record login
            LoginHistory::create([
                'user_id' => $user->user_id,
                'login_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device' => $device,
                'location' => $location,
                'status' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login recorded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Record login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record login'
            ], 500);
        }
    }

    // Parse user agent string to get device info
    private function parseUserAgent($userAgent)
    {
        if (!$userAgent) return 'Unknown';
        
        $device = 'Unknown';
        
        // Check for common devices
        if (strpos($userAgent, 'iPhone') !== false) {
            $device = 'iPhone';
        } elseif (strpos($userAgent, 'iPad') !== false) {
            $device = 'iPad';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $device = 'Android Device';
        } elseif (strpos($userAgent, 'Windows') !== false) {
            $device = 'Windows PC';
        } elseif (strpos($userAgent, 'Macintosh') !== false) {
            $device = 'Mac';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $device = 'Linux PC';
        }
        
        // Check for browser
        if (strpos($userAgent, 'Chrome') !== false) {
            $device .= ' (Chrome)';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $device .= ' (Firefox)';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $device .= ' (Safari)';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $device .= ' (Edge)';
        }
        
        return $device ?: 'Unknown';
    }
}