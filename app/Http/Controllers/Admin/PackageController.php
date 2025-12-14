<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageImage;
use App\Models\PackagePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PackageController extends Controller
{
    /**
     * Display a listing of packages.
     */
    public function index()
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $packages = Package::with(['images', 'prices'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Packages/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Admin/Packages/Create');
    }

    /**
     * Store a newly created package in storage.
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'details' => 'required|string',
            'is_active' => 'boolean',
            'images' => 'array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'weekday_price' => 'required|numeric|min:0',
            'weekend_price' => 'required|numeric|min:0',
            'weekday_days' => 'array',
            'weekend_days' => 'array',
            'special_prices' => 'array',
            'special_prices.*.price' => 'numeric|min:0',
            'special_prices.*.start_date' => 'date',
            'special_prices.*.end_date' => 'date|after_or_equal:special_prices.*.start_date',
        ]);

        DB::beginTransaction();

        try {
            // Create package
            $package = Package::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'details' => $validated['details'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Upload images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('package-images/' . $package->id, 'public');
                    
                    PackageImage::create([
                        'package_id' => $package->id,
                        'image_path' => $path,
                        'order' => $index,
                    ]);
                }
            }

            // Create weekday price
            PackagePrice::create([
                'package_id' => $package->id,
                'price_type' => 'weekday',
                'price' => $validated['weekday_price'],
                'days' => json_encode($validated['weekday_days'] ?? [1, 2, 3, 4]), // Mon-Thu
            ]);

            // Create weekend price
            PackagePrice::create([
                'package_id' => $package->id,
                'price_type' => 'weekend',
                'price' => $validated['weekend_price'],
                'days' => json_encode($validated['weekend_days'] ?? [5, 6, 0]), // Fri-Sun
            ]);

            // Create special prices
            if (!empty($validated['special_prices'])) {
                foreach ($validated['special_prices'] as $specialPrice) {
                    PackagePrice::create([
                        'package_id' => $package->id,
                        'price_type' => 'date_range',
                        'price' => $specialPrice['price'],
                        'start_date' => $specialPrice['start_date'],
                        'end_date' => $specialPrice['end_date'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.packages.index')
                ->with('success', 'Package created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create package: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified package.
     */
    public function show(Package $package)
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $package->load(['images', 'prices']);

        return Inertia::render('Admin/Packages/Show', [
            'package' => $package,
        ]);
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $package->load(['images', 'prices']);

        return Inertia::render('Admin/Packages/Edit', [
            'package' => $package,
        ]);
    }

    /**
     * Update the specified package in storage.
     */
    public function update(Request $request, Package $package)
    {
        // Check if user is admin
        if (!auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'details' => 'required|string',
            'is_active' => 'boolean',
            'new_images' => 'array|max:10',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'array',
            'weekday_price' => 'required|numeric|min:0',
            'weekend_price' => 'required|numeric|min:0',
            'weekday_days' => 'array',
            'weekend_days' => 'array',
            'special_prices' => 'array',
            'special_prices.*.id' => 'nullable|exists:package_prices,id',
            'special_prices.*.price' => 'numeric|min:0',
            'special_prices.*.start_date' => 'date',
            'special_prices.*.end_date' => 'date|after_or_equal:special_prices.*.start_date',
        ]);

        DB::beginTransaction();

        try {
            // Update package
            $package->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'details' => $validated['details'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Delete removed images
            if (!empty($validated['deleted_images'])) {
                foreach ($validated['deleted_images'] as $imageId) {
                    $image = PackageImage::find($imageId);
                    if ($image) {
                        // Delete file from storage
                        Storage::disk('public')->delete($image->image_path);
                        $image->delete();
                    }
                }
            }

            // Upload new images
            if ($request->hasFile('new_images')) {
                $existingImagesCount = $package->images()->count();
                foreach ($request->file('new_images') as $index => $image) {
                    $path = $image->store('package-images/' . $package->id, 'public');
                    
                    PackageImage::create([
                        'package_id' => $package->id,
                        'image_path' => $path,
                        'order' => $existingImagesCount + $index,
                    ]);
                }
            }

            // Update weekday price
            $weekdayPrice = $package->prices()->where('price_type', 'weekday')->first();
            if ($weekdayPrice) {
                $weekdayPrice->update([
                    'price' => $validated['weekday_price'],
                    'days' => json_encode($validated['weekday_days'] ?? [1, 2, 3, 4]),
                ]);
            }

            // Update weekend price
            $weekendPrice = $package->prices()->where('price_type', 'weekend')->first();
            if ($weekendPrice) {
                $weekendPrice->update([
                    'price' => $validated['weekend_price'],
                    'days' => json_encode($validated['weekend_days'] ?? [5, 6, 0]),
                ]);
            }

            // Handle special prices
            $existingSpecialPriceIds = $package->prices()
                ->where('price_type', 'date_range')
                ->pluck('id')
                ->toArray();

            $updatedSpecialPriceIds = [];
            if (!empty($validated['special_prices'])) {
                foreach ($validated['special_prices'] as $specialPriceData) {
                    if (isset($specialPriceData['id'])) {
                        // Update existing special price
                        $specialPrice = PackagePrice::find($specialPriceData['id']);
                        if ($specialPrice) {
                            $specialPrice->update([
                                'price' => $specialPriceData['price'],
                                'start_date' => $specialPriceData['start_date'],
                                'end_date' => $specialPriceData['end_date'],
                            ]);
                            $updatedSpecialPriceIds[] = $specialPriceData['id'];
                        }
                    } else {
                        // Create new special price
                        $newSpecialPrice = PackagePrice::create([
                            'package_id' => $package->id,
                            'price_type' => 'date_range',
                            'price' => $specialPriceData['price'],
                            'start_date' => $specialPriceData['start_date'],
                            'end_date' => $specialPriceData['end_date'],
                        ]);
                        $updatedSpecialPriceIds[] = $newSpecialPrice->id;
                    }
                }
            }

            // Delete removed special prices
            $pricesToDelete = array_diff($existingSpecialPriceIds, $updatedSpecialPriceIds);
            if (!empty($pricesToDelete)) {
                PackagePrice::whereIn('id', $pricesToDelete)->delete();
            }

            DB::commit();

            return redirect()->route('admin.packages.index')
                ->with('success', 'Package updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update package: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified package from storage.
     */
    public function destroy(Package $package)
    {
        // Only Super Admin can delete packages
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Only Super Admin can delete packages.');
        }

        DB::beginTransaction();

        try {
            // Delete images from storage
            foreach ($package->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete the package (will cascade delete related records)
            $package->delete();

            DB::commit();

            return redirect()->route('admin.packages.index')
                ->with('success', 'Package deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete package: ' . $e->getMessage());
        }
    }
}