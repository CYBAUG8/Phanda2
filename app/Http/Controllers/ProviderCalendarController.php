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




    public function events()
{
    $user = Auth::user();
    if (!$user || !$user->providerProfile) {
        return response()->json([]); // or return error response
    }

    $providerId = $user->providerProfile->provider_id;

    $serviceRequests = ServiceRequest::with(['service', 'customer'])
        ->where('provider_id', $providerId)
        ->get();

    $events = $serviceRequests->map(function ($request) {
        $color = match ($request->status) {
            'confirmed' => '#f97316',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            default     => '#9ca3af',
        };

       

        

        return [
            'id'               => $request->booking_id,
            'title'            => $request->service->title,
            'start' => date('Y-m-d', strtotime($request->booking_date))
                        . 'T' .
                        date('H:i:s', strtotime($request->start_time)),

            'end'   => date('Y-m-d', strtotime($request->booking_date))
                        . 'T' .
                        date('H:i:s', strtotime($request->end_time)),
            'backgroundColor'  => $color,
            'borderColor'      => $color,
            'textColor'        => '#ffffff',
            'extendedProps'    => [
                'customer_name' => $request->customer->full_name ?? 'N/A',
                'phone'         => $request->customer->phone ?? 'N/A',
                'address'       => $request->customer->address ?? $request->address, 
                'status'        => $request->status,
                'price'         => $request->total_price,
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