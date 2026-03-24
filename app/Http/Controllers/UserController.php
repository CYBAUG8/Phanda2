<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class UserController extends Controller
{
    public function getUserInfo(Request $request)
    {
        $user = auth()->user(); 

        return response()->json([
            'user' => [
                'full_name' => $user->full_name,
                'email'    => $user->email,
                'phone'    => $user->phone,
            ]
        ]);
    }

    public function updateUserInfo(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string',
        ]);

        if ($request->field == 'full_name') {
            $user->full_name = $request->input('value');
        }
         elseif ($request->field == 'email' || $request->field == 'phone') 
            {
             
            $request->validate([
                'otp' => 'required|digits:6',
            ]);

            $cachedOtp = Cache::get("otp_{$user->user_id}");

            if (!$cachedOtp || $cachedOtp != $request->otp) {
                return response()->json([
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }
           
            Cache::forget("otp_{$user->user_id}");
            
            if ($request->field == 'email') {
                $user->email = $request->input('value');
            } else {
                $user->phone = $request->input('value');
            }
        }
            
        $user->save();
        
        return response()->json([
            'message' => 'User information updated successfully',
            'user' => $user
        ]);
    }

 
    public function sendOtp(Request $request)
    {
        $user = $request->user();

        $request->validate([
        'field' => 'required|in:email,phone',
        'value' => 'required|string'
        ]);

        $otp = rand(100000, 999999);

        Cache::put(
            "otp_{$user->user_id}",
            $otp,
            now()->addMinutes(10)
        );

   

        if ($request->field === 'email') {
           Mail::to($request->value)->send(new OtpMail($otp));
        }
if ($request->field === 'phone') {
        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $twilio->messages->create(
                $request->value,
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Your Phanda verification code is: {$otp}. It expires in 10 minutes. Do not share this code with anyone.",
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send OTP SMS', [
                'user_id' => $user->user_id,
                'phone'   => $request->value,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send OTP via SMS. Please try again.',
            ], 500);
        }
    }

         return response()->json([
            'message' => 'OTP sent successfully',
        ]);
        

    }

    // Update password
     public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

       
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'New password must be different from current password'
            ], 400);
        }

       
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    // Delete account
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
       
        $request->validate([
            'password' => 'required|string',
        ]);
        
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password is incorrect'
            ], 400);
        }
        
   
         $user->delete();
         $user->is_deleted = true;
         $user->save();
        
        Auth::logout();
        
        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }
public function downloadData(Request $request)
{
    $user = $request->user();

    $emergencyContact = $user->emergencyContact;
    $locations        = $user->locations;
    $loginHistories   = $user->loginHistories()->latest()->take(10)->get();

    $bookings = \App\Models\ServiceRequest::where('user_id', $user->user_id)
                    ->with('service.category')
                    ->latest('booking_date')
                    ->get();

    // ── Financial summaries ───────────────────────────────────────
    $completedBookings = $bookings->where('status', 'completed');
    $pendingBookings   = $bookings->whereIn('status', ['pending', 'confirmed']);

    $totalSpent = $completedBookings->sum('total_price');

    // Breakdown by category
    $categoryBreakdown = $completedBookings
        ->groupBy(fn($b) => $b->service?->category?->name ?? 'Uncategorised')
        ->map(fn($group) => [
            'count'  => $group->count(),
            'total'  => $group->sum('total_price'),
        ])
        ->sortByDesc(fn($item) => $item['total']);

    // Monthly spending summary (last 12 months)
    $monthlySpending = $completedBookings
        ->filter(fn($b) => \Carbon\Carbon::parse($b->booking_date)->gte(now()->subMonths(12)))
        ->groupBy(fn($b) => \Carbon\Carbon::parse($b->booking_date)->format('M Y'))
        ->map(fn($group) => [
            'count' => $group->count(),
            'total' => $group->sum('total_price'),
        ]);

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.user_data', compact(
        'user',
        'emergencyContact',
        'locations',
        'loginHistories',
        'bookings',
        'completedBookings',
        'pendingBookings',
        'totalSpent',
        'categoryBreakdown',
        'monthlySpending'
    ));

    $pdf->setPaper('A4', 'portrait');

    return $pdf->download('phanda-my-data-' . now()->format('Y-m-d') . '.pdf');
}
    
}