<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sport;

class SportController extends Controller
{
    /**
     * GET /api/sport/{id}/pricing
     * Public: Get pricing by sport.
     */
    public function pricing(int $id)
    {
        $sport = Sport::with(['pricing_tables', 'facility', 'district'])->findOrFail($id);

        return response()->json([
            'sport' => $sport->only(['id', 'name']),
            'facility' => $sport->facility,
            'district' => $sport->district,
            'pricing' => $sport->pricing_tables,
        ]);
    }
}
