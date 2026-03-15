<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Facility;

class DistrictController extends Controller
{
    /**
     * GET /api/districts
     * Public: Get all districts.
     */
    public function index()
    {
        $districts = District::all();

        return response()->json([
            'districts' => $districts,
        ]);
    }

    /**
     * GET /api/district/{id}
     * Public: Get district details.
     */
    public function show(int $id)
    {
        $district = District::findOrFail($id);

        return response()->json([
            'district' => $district,
        ]);
    }

    /**
     * GET /api/district/{id}/facilities
     * Public: Get facilities by district.
     */
    public function facilities(int $id)
    {
        $district = District::findOrFail($id);
        $facilities = $district->facilities()->with('sports.pricing_tables')->get();

        return response()->json([
            'facilities' => $facilities,
        ]);
    }

    /**
     * GET /api/district/{id}/sports
     * Public: Get sports by district.
     */
    public function sports(int $id)
    {
        $district = District::findOrFail($id);
        $sports = $district->sports()->with('facility')->get();

        return response()->json([
            'sports' => $sports,
        ]);
    }

    /**
     * GET /api/facilities
     * Public: Get all facilities across all districts.
     */
    public function allFacilities()
    {
        // Get unique facilities by name to avoid repetition in general listings
        $facilities = Facility::with('district:id,name')
            ->orderBy('name')
            ->get()
            ->unique('name') // Use Laravel collection helper to get one of each name
            ->values(); // Reset array indices

        return response()->json([
            'facilities' => $facilities,
        ]);
    }
    /**
     * GET /api/facility/{slug}
     * Public: Get facility details by slug.
     */
    public function getFacilityBySlug(string $slug)
    {
        $facility = Facility::where('slug', $slug)
            ->with(['district', 'sports.pricing_tables'])
            ->firstOrFail();

        return response()->json([
            'facility' => $facility,
        ]);
    }
}
