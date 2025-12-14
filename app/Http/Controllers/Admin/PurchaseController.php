<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    /**
     * Display a listing of all purchases.
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $query = Purchase::with(['user', 'package'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by package
        if ($request->has('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search by user email or name
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            })->orWhere('stripe_payment_id', 'like', "%{$search}%");
        }

        $purchases = $query->paginate(20);

        $packages = Package::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Purchases/Index', [
            'purchases' => $purchases,
            'packages' => $packages,
            'filters' => $request->only(['search', 'status', 'package_id', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $purchase->load(['user', 'package.images', 'package.prices']);

        return Inertia::render('Admin/Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Update purchase status (for refunds, etc.)
     */
    public function updateStatus(Request $request, Purchase $purchase)
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $purchase->update([
            'status' => $validated['status'],
        ]);

        // Add to payment details
        $paymentDetails = $purchase->payment_details ?? [];
        $paymentDetails['status_updates'][] = [
            'status' => $validated['status'],
            'updated_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
            'updated_at' => now()->toDateTimeString(),
        ];

        $purchase->update([
            'payment_details' => $paymentDetails,
        ]);

        return back()->with('success', 'Purchase status updated successfully.');
    }

    /**
     * Get purchase statistics
     */
    public function statistics()
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $stats = [
            'total_purchases' => Purchase::count(),
            'total_revenue' => Purchase::where('status', 'completed')->sum('amount'),
            'pending_purchases' => Purchase::where('status', 'pending')->count(),
            'completed_purchases' => Purchase::where('status', 'completed')->count(),
            'failed_purchases' => Purchase::where('status', 'failed')->count(),
            'refunded_purchases' => Purchase::where('status', 'refunded')->count(),
            'recent_purchases' => Purchase::with(['user', 'package'])
                ->latest()
                ->take(10)
                ->get(),
            'top_packages' => Purchase::selectRaw('package_id, count(*) as count, sum(amount) as revenue')
                ->where('status', 'completed')
                ->with('package')
                ->groupBy('package_id')
                ->orderBy('revenue', 'desc')
                ->take(5)
                ->get(),
        ];

        return Inertia::render('Admin/Purchases/Statistics', [
            'stats' => $stats,
        ]);
    }
}