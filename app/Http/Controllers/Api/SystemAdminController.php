<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SystemAdminController extends Controller
{
    /**
     * POST /api/systemadmin/create-subadmin
     * System Admin: Create a Sub Admin with assigned district.
     */
    public function createSubAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', Password::min(8)],
            'district_id' => 'required|exists:districts,id',
        ]);

        $subAdmin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'sub_admin',
            'district_id' => $validated['district_id'],
        ]);

        return response()->json([
            'message' => 'Sub Admin created successfully.',
            'sub_admin' => $subAdmin->load('district'),
        ], 201);
    }

    /**
     * GET /api/systemadmin/all-bookings
     * System Admin: View all bookings across all districts.
     */
    public function allBookings(Request $request)
    {
        $query = Booking::with(['user:id,name,email', 'facility:id,name', 'sport:id,name', 'district:id,name'])
            ->orderBy('created_at', 'desc');

        // Optional filter by district
        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Optional filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(20);

        return response()->json($bookings);
    }

    /**
     * GET /api/systemadmin/districts
     * System Admin: List all districts with sub-admin count.
     */
    public function districts()
    {
        $districts = District::withCount('subAdmins')->get();

        return response()->json([
            'districts' => $districts,
        ]);
    }

    /**
     * POST /api/systemadmin/district
     * System Admin: Create a new district.
     */
    public function createDistrict(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:districts',
            'address' => 'nullable|string',
            'contact' => 'nullable|string|max:255',
            'working_hours' => 'nullable|string|max:255',
        ]);

        $district = District::create($validated);

        return response()->json([
            'message' => 'District created successfully.',
            'district' => $district,
        ], 201);
    }

    /**
     * PUT /api/systemadmin/district/{id}
     * System Admin: Update a district.
     */
    public function updateDistrict(Request $request, int $id)
    {
        $district = District::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:districts,name,' . $id,
            'address' => 'nullable|string',
            'contact' => 'nullable|string|max:255',
            'working_hours' => 'nullable|string|max:255',
        ]);

        $district->update($validated);

        return response()->json([
            'message' => 'District updated successfully.',
            'district' => $district,
        ]);
    }

    /**
     * GET /api/systemadmin/sub-admins
     * System Admin: List all sub-admins.
     */
    public function subAdmins()
    {
        $subAdmins = User::where('role', 'sub_admin')
            ->with('district')
            ->get();

        return response()->json([
            'sub_admins' => $subAdmins,
        ]);
    }

    /**
     * DELETE /api/systemadmin/sub-admin/{id}
     * System Admin: Remove a sub-admin.
     */
    public function deleteSubAdmin(int $id)
    {
        $user = User::where('id', $id)->where('role', 'sub_admin')->firstOrFail();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Sub Admin deleted successfully.',
        ]);
    }

    /**
     * GET /api/systemadmin/users
     * System Admin: List all registered users.
     */
    public function users()
    {
        $users = User::where('role', 'user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * DELETE /api/systemadmin/user/{id}
     * System Admin: Remove a user account.
     */
    public function deleteUser(int $id)
    {
        $user = User::where('id', $id)->where('role', 'user')->firstOrFail();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * PUT /api/systemadmin/user/{id}
     * System Admin: Update a user account.
     */
    public function updateUser(Request $request, int $id)
    {
        $user = User::where('id', $id)->where('role', 'user')->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'district_id' => 'nullable|exists:districts,id',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->load('district'),
        ]);
    }

    /**
     * GET /api/systemadmin/dashboard
     * System Admin: Dashboard statistics.
     */
    public function dashboard()
    {
        return response()->json([
            'total_districts' => District::count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_sub_admins' => User::where('role', 'sub_admin')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'rejected_bookings' => Booking::where('status', 'rejected')->count(),
        ]);
    }
}
