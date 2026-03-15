<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * POST /api/check-availability
     * User: Check if a time slot is available.
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'facility_id' => 'required|exists:facilities,id',
            'sport_id' => 'nullable|exists:sports,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'booking_mode' => 'nullable|string|in:full,half,slot',
            'slots' => 'nullable|array',
            'slots.*' => 'integer|min:1',
            'total_slots' => 'nullable|integer|min:1',
        ]);

        $bookingMode = $validated['booking_mode'] ?? 'full';
        $requestedSlots = $validated['slots'] ?? null;
        $totalSlots = $validated['total_slots'] ?? null;

        // Get already booked slots for this facility/time
        $bookedSlots = Booking::getBookedSlots(
            $validated['facility_id'],
            $validated['booking_date'],
            $validated['start_time'],
            $validated['end_time']
        );

        // For full booking mode, check if ANY slot is booked
        if ($bookingMode === 'full' && $totalSlots) {
            $allSlots = range(1, $totalSlots);
            $unavailableSlots = array_intersect($allSlots, $bookedSlots);
            if (count($unavailableSlots) > 0) {
                return response()->json([
                    'available' => false,
                    'message' => 'Some slots are already booked. Full booking is not available.',
                    'booked_slots' => array_values($bookedSlots),
                ]);
            }
            return response()->json([
                'available' => true,
                'message' => 'All slots are available for full booking.',
                'booked_slots' => [],
            ]);
        }

        // For half/slot mode, check specific requested slots
        if ($requestedSlots && count($requestedSlots) > 0) {
            $conflicting = array_values(array_intersect($requestedSlots, $bookedSlots));
            if (count($conflicting) > 0) {
                return response()->json([
                    'available' => false,
                    'message' => 'Some of your selected slots are already booked.',
                    'booked_slots' => array_values($bookedSlots),
                ]);
            }
            return response()->json([
                'available' => true,
                'message' => 'Your selected slots are available for booking.',
                'booked_slots' => array_values($bookedSlots),
            ]);
        }

        // Fallback: facility-level check (no slot info provided)
        $hasOverlap = Booking::hasOverlap(
            $validated['facility_id'],
            $validated['booking_date'],
            $validated['start_time'],
            $validated['end_time']
        );

        return response()->json([
            'available' => !$hasOverlap,
            'message' => $hasOverlap
                ? 'This time slot is not available. There is an existing confirmed booking.'
                : 'This time slot is available for booking.',
            'booked_slots' => array_values($bookedSlots),
        ]);
    }

    /**
     * POST /api/bookings
     * User: Create a new booking request (status = pending).
     */
    public function store(Request $request)
    {
        $user = $request->user(); // may be null for guests

        $validated = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'facility_id' => 'required|exists:facilities,id',
            'sport_id' => 'nullable|exists:sports,id',
            'organization_type' => 'nullable|string|max:255',
            'event_type' => 'nullable|string|max:255',
            'booking_mode' => 'nullable|string|in:full,half,slot',
            'slots' => 'nullable|array',
            'slots.*' => 'integer|min:1',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_end_date' => 'nullable|date|after_or_equal:booking_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'price' => 'nullable|numeric|min:0',
            // Guest fields (required when not logged in)
            'guest_name' => $user ? 'nullable|string|max:255' : 'required|string|max:255',
            'guest_email' => $user ? 'nullable|email|max:255' : 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
        ]);

        $requestedSlots = $validated['slots'] ?? null;

        // Double check availability before booking
        $hasOverlap = Booking::hasOverlap(
            $validated['facility_id'],
            $validated['booking_date'],
            $validated['start_time'],
            $validated['end_time'],
            null,
            $validated['booking_end_date'] ?? null,
            $requestedSlots
        );

        if ($hasOverlap) {
            return response()->json([
                'message' => 'This time slot has already been booked. Please choose another time.',
            ], 409);
        }

        $booking = Booking::create([
            'user_id' => $user?->id,
            'guest_name' => $validated['guest_name'] ?? ($user?->name),
            'guest_email' => $validated['guest_email'] ?? ($user?->email),
            'guest_phone' => $validated['guest_phone'] ?? null,
            'district_id' => $validated['district_id'],
            'facility_id' => $validated['facility_id'],
            'sport_id' => $validated['sport_id'] ?? null,
            'organization_type' => $validated['organization_type'] ?? null,
            'event_type' => $validated['event_type'] ?? null,
            'booking_mode' => $validated['booking_mode'] ?? 'full',
            'slots' => $requestedSlots,
            'booking_date' => $validated['booking_date'],
            'booking_end_date' => $validated['booking_end_date'] ?? $validated['booking_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'price' => $validated['price'] ?? 0,
            'status' => 'pending',
        ]);

        // Notify Sub-Admins in the district
        try {
            $subAdmins = \App\Models\User::where('role', 'sub_admin')
                ->where('district_id', $validated['district_id'])
                ->get();
            
            if ($subAdmins->count() > 0) {
                foreach ($subAdmins as $admin) {
                    \Illuminate\Support\Facades\Mail::to($admin->email)->send(new \App\Mail\NewBookingRequest($booking));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to notify sub-admins: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Booking request submitted successfully.',
            'booking' => $booking->load(['facility', 'sport', 'district']),
        ], 201);
    }

    /**
     * GET /api/my-bookings
     * User: Get all bookings for the authenticated user.
     */
    public function myBookings(Request $request)
    {
        $user = $request->user();

        $bookings = Booking::with(['facility', 'sport', 'district'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('guest_email', $user->email);
            })
            ->orderBy('booking_date', 'desc')
            ->get();

        return response()->json([
            'bookings' => $bookings,
        ]);
    }
}
