<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class ProviderCalendarController extends Controller
{
    //
    public function index(){

      return view('provider.schedule');
    }

    public function events(){

        $providerId=Auth::User()->ProviderProfile->provider_id;

        $ServiceRequest=ServiceRequest::with(['service','customer'])
           ->where('provider_id',$providerId)
           ->get();

        $events=$ServiceRequest->map(function($ServiceRequest){

            $color =match($ServiceRequest->status){

                'confirmed'=>'#f97316',
                'completed' => '#10b981',
                'cancelled' => '#ef4444',
                 default => '#9ca3af',



            };

            return[

                'id'=>$ServiceRequest->booking_id,
                'title'=>$ServiceRequest->service->title,
                'start'=>$ServiceRequest->start_time,
                'end'=>$ServiceRequest->end_time,
                'backgroundColor'=>$color,
                'borderColor'=>$color,
                'textColor'=>'#ffffff',
                'extendedProps'=>[

                    'customer_name'=>$ServiceRequest->customer->full_name,
                    'phone'=>$ServiceRequest->customer->phone,
                    'address'=>$ServiceRequest->customer->address,
                    'notes'=>$ServiceRequest->notes,
                    'status'=>$ServiceRequest->status,
                    'price'=>$ServiceRequest->total_price,
                ]


            ];

        });
        return response()->json($events);
    }





 public function updateStatus(Request $request, $bookingId)
 {

    $ServiceRequest=ServiceRequest::findOrFail($bookingId);

    $ServiceRequest ->update([
        'status'=> $request->status
    ]);

    return response()->json(['success'=>true]);
   
}

}