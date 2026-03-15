<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function subscriptions(Request $request)
    {
        $subscriptions = Subscription::with('facility')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        // Determine overall subscription status
        $active = $subscriptions->where('status', 'active')->isNotEmpty();
        $status = $active ? 'active' : ($subscriptions->isNotEmpty() ? 'expired' : 'none');

        return response()->json([
            'subscriptions' => $subscriptions,
            'subscription_status' => $status,
        ]);
    }

    public function createSubscription(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'frequency' => 'required|in:monthly,yearly',
            'price' => 'required|numeric|min:0',
        ]);

        $subscription = Subscription::create([
            'user_id' => $request->user()->id,
            'facility_id' => $request->facility_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'frequency' => $request->frequency,
            'price' => $request->price,
            'status' => 'active',
        ]);

        $subscription->load('facility');

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription' => $subscription,
        ], 201);
    }

    public function cancelSubscription(Request $request, $id)
    {
        $subscription = Subscription::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $subscription->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Subscription cancelled']);
    }

    public function transactions(Request $request)
    {
        $transactions = Transaction::with(['subscription.facility'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('payment_date')
            ->get();

        return response()->json(['transactions' => $transactions]);
    }
}
