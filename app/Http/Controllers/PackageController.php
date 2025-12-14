<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageController extends Controller
{
    /**
     * Display a listing of packages for users.
     */
    public function index(Request $request)
    {
        $query = Package::with(['images'])
            ->where('is_active', true)
            ->latest();

        // Search packages
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->whereHas('prices', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }

        if ($request->has('max_price')) {
            $query->whereHas('prices', function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        $packages = $query->paginate(12);

        // Get price range for filter
        $minPrice = Package::where('is_active', true)
            ->join('package_prices', 'packages.id', '=', 'package_prices.package_id')
            ->min('package_prices.price');
            
        $maxPrice = Package::where('is_active', true)
            ->join('package_prices', 'packages.id', '=', 'package_prices.package_id')
            ->max('package_prices.price');

        return Inertia::render('Packages/Index', [
            'packages' => $packages,
            'filters' => $request->only(['search', 'min_price', 'max_price']),
            'priceRange' => [
                'min' => $minPrice ?? 0,
                'max' => $maxPrice ?? 1000,
            ],
        ]);
    }

    /**
     * Display the specified package for users.
     */
    public function show(Package $package)
    {
        // Check if package is active
        if (!$package->is_active && !auth()->user()?->hasRole(['Admin', 'Super Admin'])) {
            abort(404, 'Package not found.');
        }

        $package->load(['images', 'prices']);

        // Get current price based on today's date
        $currentPrice = $package->current_price;

        // Get all available prices
        $weekdayPrice = $package->prices()->where('price_type', 'weekday')->first();
        $weekendPrice = $package->prices()->where('price_type', 'weekend')->first();
        $specialPrices = $package->prices()
            ->where('price_type', 'date_range')
            ->whereDate('end_date', '>=', now())
            ->orderBy('start_date')
            ->get();

        // Get related packages
        $relatedPackages = Package::where('is_active', true)
            ->where('id', '!=', $package->id)
            ->with(['images'])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return Inertia::render('Packages/Show', [
            'package' => $package,
            'currentPrice' => $currentPrice,
            'weekdayPrice' => $weekdayPrice,
            'weekendPrice' => $weekendPrice,
            'specialPrices' => $specialPrices,
            'relatedPackages' => $relatedPackages,
        ]);
    }

    /**
     * Get package availability for calendar.
     */
    public function availability(Package $package, Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);

        $dates = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Check for special price on this date
            $specialPrice = $package->prices()
                ->where('price_type', 'date_range')
                ->whereDate('start_date', '<=', $currentDate)
                ->whereDate('end_date', '>=', $currentDate)
                ->first();

            if ($specialPrice) {
                $price = $specialPrice->price;
                $priceType = 'special';
            } else {
                // Check if it's weekend
                $isWeekend = in_array($currentDate->dayOfWeek, [5, 6, 0]); // Fri, Sat, Sun
                $priceType = $isWeekend ? 'weekend' : 'weekday';
                
                $regularPrice = $package->prices()
                    ->where('price_type', $priceType)
                    ->first();
                    
                $price = $regularPrice ? $regularPrice->price : 0;
            }

            $dates[] = [
                'date' => $currentDate->toDateString(),
                'price' => $price,
                'price_type' => $priceType,
                'available' => true, // You can add availability logic here
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'dates' => $dates,
            'total_days' => count($dates),
            'total_price' => array_sum(array_column($dates, 'price')),
        ]);
    }
}