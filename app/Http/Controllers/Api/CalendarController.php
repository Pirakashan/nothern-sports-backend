<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * GET /api/calendar?district_id=1
     * Public/Auth: Return confirmed bookings for calendar display.
     */
    public function index(Request $request)
    {
        $request->validate([
            'district_id' => 'required|exists:districts,id',
        ]);

        $bookings = Booking::where('district_id', $request->district_id)
            ->where('status', 'confirmed')
            ->with(['user:id,name', 'facility:id,name,slug', 'sport:id,name'])
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($booking) {
                // Handle different types of date/time values
                $bookingDate = $booking->booking_date;
                $bookingEndDate = $booking->booking_end_date ?? $bookingDate;
                $startTime = $booking->start_time;
                $endTime = $booking->end_time;
                
                // Format dates safely
                $dateStr = is_string($bookingDate) 
                    ? $bookingDate 
                    : $bookingDate->format('Y-m-d');
                    
                $endDateStr = is_string($bookingEndDate)
                    ? $bookingEndDate
                    : $bookingEndDate->format('Y-m-d');
                    
                $startTimeStr = is_string($startTime) 
                    ? substr($startTime, 0, 5)  // HH:mm from HH:mm:ss
                    : $startTime->format('H:i');
                    
                $endTimeStr = is_string($endTime)
                    ? substr($endTime, 0, 5)  // HH:mm from HH:mm:ss
                    : $endTime->format('H:i');
                
                return [
                    'id' => $booking->id,
                    'user_name' => $booking->user?->name ?? $booking->guest_name ?? 'Guest',
                    'facility' => $booking->facility?->name ?? 'N/A',
                    'facility_slug' => $booking->facility?->slug ?? '',
                    'sport' => $booking->sport?->name ?? 'N/A',
                    'date' => $dateStr,
                    'end_date' => $endDateStr,
                    'start_time' => $startTimeStr,
                    'end_time' => $endTimeStr,
                    'status' => $booking->status,
                ];
            });

        return response()->json([
            'calendar' => $bookings,
        ]);
    }
}
