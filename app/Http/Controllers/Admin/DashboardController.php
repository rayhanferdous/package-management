<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $stats = [
            'total_packages' => Package::count(),
            'active_packages' => Package::where('is_active', true)->count(),
            'total_purchases' => Purchase::count(),
            'recent_purchases' => Purchase::with(['user', 'package'])
                ->latest()
                ->take(5)
                ->get(),
            'total_revenue' => Purchase::where('status', 'completed')->sum('amount'),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}