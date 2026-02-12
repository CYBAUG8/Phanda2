<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    //

    public function sendOtp(Request $request){

       $user=$request->user();

       $otp =rand(100000,999999);

       Cache::put(
        "otp_{$user->user_id}",
        $otp,
        now()->addMinutes(10)

       );
        
      return response()->json([
        'message'=>'OTP sent successfully',
        'otp'=>$otp
        
      ]);
    }
}