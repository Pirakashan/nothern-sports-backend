<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\PricingTable;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmed;

class SubAdminController extends Controller
{
    /**
     * POST /api/subadmin/facility
     * Sub Admin: Add a facility to their district.
     */
    public function addFacility(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'district_id' => 'nullable|exists:districts,id',
        ]);

        // Parse sports from JSON string (sent via FormData)
        $sportsData = [];
        if ($request->has('sports')) {
            $sportsData = is_string($request->sports) ? json_decode($request->sports, true) : $request->sports;
        }

        $districtId = $request->user()->district_id ?? $request->district_id;

        if (!$districtId) {
            return response()->json([
                'message' => 'District ID is required. Your account may not be assigned to a district.'
            ], 400);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('facilities', $filename, 'public');
            $imagePath = '/storage/facilities/' . $filename;
        }

        return \DB::transaction(function () use ($validated, $districtId, $imagePath, $sportsData) {
            // Generate unique slug by appending district ID to name
            $slug = Str::slug($validated['name']) . '-' . $districtId;

            $facility = Facility::create([
                'district_id' => $districtId,
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
            ]);

            if (!empty($sportsData)) {
                foreach ($sportsData as $sportItem) {
                    $sport = Sport::create([
                        'district_id' => $districtId,
                        'facility_id' => $facility->id,
                        'name' => $sportItem['name'],
                    ]);

                    if (!empty($sportItem['pricing'])) {
                        foreach ($sportItem['pricing'] as $priceData) {
                            PricingTable::create([
                                'district_id' => $districtId,
                                'sport_id' => $sport->id,
                                'type' => $priceData['type'],
                                'billing_type' => $priceData['billing_type'] ?? 'hourly',
                                'price_per_hour' => $priceData['price_per_hour'] ?? $priceData['price'] ?? 0,
                                'price_per_day' => $priceData['price_per_day'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'message' => 'Facility, sports, and pricing added successfully.',
                'facility' => $facility->load('sports.pricing_tables'),
            ], 201);
        });
    }

    /**
     * POST /api/subadmin/sport
     * Sub Admin: Add a sport to their district.
     */
    public function addSport(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'name' => 'required|string|max:255',
        ]);

        // Verify facility belongs to sub-admin's district (if not system admin)
        $query = Facility::where('id', $validated['facility_id']);
        
        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $facility = $query->firstOrFail();

        $sport = Sport::create([
            'district_id' => $facility->district_id,
            'facility_id' => $facility->id,
            'name' => $validated['name'],
        ]);

        // Create default pricing entries if provided
        if ($request->has('pricing')) {
            $pricingData = is_string($request->pricing) ? json_decode($request->pricing, true) : $request->pricing;
            if (is_array($pricingData)) {
                foreach ($pricingData as $priceItem) {
                    if (!empty($priceItem['type'])) {
                        PricingTable::create([
                            'district_id' => $facility->district_id,
                            'sport_id' => $sport->id,
                            'type' => $priceItem['type'],
                            'billing_type' => $priceItem['billing_type'] ?? 'hourly',
                            'price_per_hour' => $priceItem['price_per_hour'] ?? $priceItem['price'] ?? 0,
                            'price_per_day' => $priceItem['price_per_day'] ?? 0,
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Sport added successfully.',
            'sport' => $sport->load(['facility', 'pricing_tables']),
        ], 201);
    }

    /**
     * DELETE /api/subadmin/sports/{id}
     * Sub Admin: Delete a sport and its pricing from their district.
     */
    public function deleteSport(Request $request, int $id)
    {
        $query = Sport::where('id', $id);

        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $sport = $query->firstOrFail();

        return \DB::transaction(function () use ($sport) {
            // Delete related pricing tables
            $sport->pricing_tables()->delete();

            // Delete related bookings for this sport
            Booking::where('sport_id', $sport->id)->delete();

            // Delete the sport
            $sport->delete();

            return response()->json([
                'message' => 'Sport and related data deleted successfully.',
            ]);
        });
    }

    /**
     * PUT /api/subadmin/facilities/{id}
     * Sub Admin: Update a facility's details.
     */
    public function updateFacility(Request $request, int $id)
    {
        $query = Facility::where('id', $id);

        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $facility = $query->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('facilities', $filename, 'public');
            $validated['image'] = '/storage/facilities/' . $filename;
        }

        // Update slug if name changed
        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . $facility->district_id;
        }

        $facility->update($validated);

        return response()->json([
            'message' => 'Facility updated successfully.',
            'facility' => $facility->load('sports.pricing_tables'),
        ]);
    }

    /**
     * POST /api/subadmin/pricing
     * Sub Admin: Add pricing for a sport.
     */
    public function addPricing(Request $request)
    {
        try {
            $validated = $request->validate([
                'sport_id' => 'required|exists:sports,id',
                'type' => 'required|in:competition,practice,refundable_deposit',
                'price_per_hour' => 'nullable|numeric|min:0',
                // Support legacy 'price' field too
                'price' => 'nullable|numeric|min:0',
            ]);

            // Verify sport belongs to sub-admin's district
            $query = Sport::where('id', $validated['sport_id']);
            
            if ($request->user()->isSubAdmin()) {
                $query->where('district_id', $request->user()->district_id);
            }

            $sport = $query->firstOrFail();

            $districtId = $request->user()->district_id ?? $sport->district_id;

            $updateData = [
                'district_id' => $districtId,
            ];

            if ($request->has('billing_type')) {
                $updateData['billing_type'] = $request->billing_type;
            }

            if ($request->has('price_per_hour')) {
                $updateData['price_per_hour'] = $request->price_per_hour;
            } elseif ($request->has('price')) {
                $updateData['price_per_hour'] = $request->price;
            }

            if ($request->has('price_per_day')) {
                $updateData['price_per_day'] = $request->price_per_day;
            }

            $pricing = PricingTable::updateOrCreate(
                [
                    'sport_id' => $sport->id,
                    'type' => $validated['type'],
                ],
                $updateData
            );

            return response()->json([
                'message' => 'Pricing updated successfully.',
                'pricing' => $pricing->load('sport'),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/subadmin/bookings
     * Sub Admin: View booking requests for their district.
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'facility', 'sport', 'district'])
            ->orderBy('created_at', 'desc');
        
        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $bookings = $query->get();

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    /**
     * PUT /api/subadmin/bookings/{id}/confirm
     * Sub Admin: Confirm a booking.
     */
    public function confirmBooking(Request $request, int $id)
    {
        $query = Booking::where('id', $id);

        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $booking = $query->firstOrFail();

        if ($booking->status === 'confirmed') {
            return response()->json([
                'message' => 'Booking is already confirmed.',
                'booking' => $booking,
            ]);
        }

        // Check for overlap before confirming
        $hasOverlap = Booking::hasOverlap(
            $booking->facility_id,
            $booking->booking_date->format('Y-m-d'),
            $booking->start_time->format('H:i'),
            $booking->end_time->format('H:i'),
            $booking->id
        );

        if ($hasOverlap) {
            return response()->json([
                'message' => 'Cannot confirm: time slot conflicts with another confirmed booking.',
            ], 409);
        }

        $booking->update(['status' => 'confirmed']);

        // Send confirmation email to the user or guest
        try {
            $email = $booking->user ? $booking->user->email : $booking->guest_email;
            if ($email) {
                \Mail::to($email)->send(new \App\Mail\BookingConfirmed($booking, $booking->user));
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send booking confirmation email for booking #{$booking->id}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Booking confirmed successfully and email sent to user.',
            'booking' => $booking->load(['user:id,name,email', 'facility:id,name', 'sport:id,name']),
        ]);
    }

    /**
     * PUT /api/subadmin/bookings/{id}/reject
     * Sub Admin: Reject a booking.
     */
    public function rejectBooking(Request $request, int $id)
    {
        $query = Booking::where('id', $id);

        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $booking = $query->firstOrFail();

        $booking->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Booking rejected.',
            'booking' => $booking,
        ]);
    }

    /**
     * GET /api/subadmin/facilities
     * Sub Admin: List facilities for their district.
     */
    public function facilities(Request $request)
    {
        $query = Facility::with(['district', 'sports.pricing_tables']);
        
        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $facilities = $query->get();

        return response()->json([
            'facilities' => $facilities,
        ]);
    }

    /**
     * DELETE /api/systemadmin/facility/{id}
     * System Admin: Delete a facility and all related data.
     */
    public function deleteFacility(Request $request, int $id)
    {
        $query = Facility::where('id', $id);

        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $facility = $query->firstOrFail();

        return \DB::transaction(function () use ($facility) {
            // Delete related pricing tables via sports
            foreach ($facility->sports as $sport) {
                $sport->pricing_tables()->delete();
            }

            // Delete related bookings
            Booking::where('facility_id', $facility->id)->delete();

            // Delete related sports
            $facility->sports()->delete();

            // Delete the facility itself
            $facility->delete();

            return response()->json([
                'message' => 'Facility and all related data deleted successfully.',
            ]);
        });
    }

    /**
     * GET /api/subadmin/sports
     * Sub Admin: List sports for their district.
     */
    public function sports(Request $request)
    {
        $query = Sport::with(['facility', 'district', 'pricing_tables']);
        
        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $sports = $query->get();

        return response()->json([
            'sports' => $sports,
        ]);
    }

    /**
     * GET /api/subadmin/dashboard
     * Sub Admin: Dashboard statistics for their district.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $isSubAdmin = $user->isSubAdmin();
        $districtId = $isSubAdmin ? $user->district_id : null;

        $totalUsersQuery = User::where('role', 'user');
        $baseBookingQuery = Booking::query();
        $revenueQuery = Booking::where('status', 'confirmed')
            ->whereMonth('booking_date', now()->month)
            ->whereYear('booking_date', now()->year);
        $facilityQuery = Facility::query();
        $recentQuery = Booking::with(['user', 'facility', 'district', 'sport'])
            ->orderBy('created_at', 'desc')
            ->limit(5);

        if ($isSubAdmin && $districtId) {
            $totalUsersQuery->where('district_id', $districtId);
            $baseBookingQuery->where('district_id', $districtId);
            $revenueQuery->where('district_id', $districtId);
            $facilityQuery->where('district_id', $districtId);
            $recentQuery->where('district_id', $districtId);
        }

        return response()->json([
            'pending_bookings' => Booking::where('district_id', $districtId)->where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('district_id', $districtId)->where('status', 'confirmed')->count(),
            'rejected_bookings' => Booking::where('district_id', $districtId)->where('status', 'rejected')->count(),
            'total_bookings' => Booking::where('district_id', $districtId)->count(),
            'total_users' => User::where('district_id', $districtId)->where('role', 'user')->count(),
            'monthly_revenue' => Booking::where('district_id', $districtId)->where('status', 'confirmed')->sum('price'),
            'active_facilities' => Facility::where('district_id', $districtId)->count(),
            'recent_bookings' => Booking::with(['user:id,name,email', 'facility:id,name', 'sport:id,name'])
                ->where('district_id', $districtId)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * GET /api/subadmin/reports
     * Sub Admin: Get approved and rejected bookings for reports.
     */
    public function reports(Request $request)
    {
        $baseQuery = Booking::query();
        $queryConfirmed = Booking::with(['user:id,name,email', 'facility:id,name', 'sport:id,name', 'district:id,name'])
            ->where('status', 'confirmed');
            
        $queryRejected = Booking::with(['user:id,name,email', 'facility:id,name', 'sport:id,name', 'district:id,name'])
            ->where('status', 'rejected');
        
        if ($request->user()->isSubAdmin()) {
            $queryConfirmed->where('district_id', $request->user()->district_id);
            $queryRejected->where('district_id', $request->user()->district_id);
        }

        return response()->json([
            'confirmed' => $queryConfirmed->get(),
            'rejected' => $queryRejected->get(),
        ]);
    }
    /**
     * GET /api/subadmin/users
     * Sub Admin: View users registered in their district.
     */
    public function users(Request $request)
    {
        $query = User::where('role', 'user');

        if ($request->user()->isSubAdmin()) {
            $query->where('district_id', $request->user()->district_id);
        }

        $users = $query->with('district:id,name')->get();

        return response()->json([
            'users' => $users,
        ]);
    }
}
