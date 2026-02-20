<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'service_id'   => 'required|exists:services,service_id',
            'provider_id'  => 'required|exists:provider_profiles,provider_id',
            'address_id'   => 'required|exists:addresses,address_id',
            'booking_date' => 'required|date',
            'start_time'   => 'required',
            'end_time'     => 'required',
            'total_price'  => 'required|numeric',
        ]);

        $booking = ServiceRequest::create([
            'user_id'      => '3f405662-e611-4eec-b510-17b3f56b5b22',
            'service_id'   => $request->service_id,
            'provider_id'  => $request->provider_id,
            'address_id'   => $request->address_id,
            'booking_date' => $request->booking_date,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
            'status'       => 'pending',
            'total_price'  => $request->total_price,
            'notes'        => $request->notes,
            'address'      => $request->address,
        ]);

        return response()->json([
            'message' => 'Service request created successfully',
            'data' => $booking
        ]);
    }
}
