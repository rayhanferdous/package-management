<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\PurchaseController as AdminPurchaseController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\SettingsController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('packages.index');
});

Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/packages/{package}/availability', [PackageController::class, 'availability'])->name('packages.availability');

// Authenticated user routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete.avatar');
    Route::get('/profile/purchases', [ProfileController::class, 'purchases'])->name('profile.purchases');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Purchase routes
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/packages/{package}/purchase', [PurchaseController::class, 'create'])->name('purchase.create');
    Route::post('/packages/{package}/purchase', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::get('/purchase/success', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/cancel', [PurchaseController::class, 'cancel'])->name('purchase.cancel');
});

// Admin routes
Route::middleware(['auth', 'verified', 'role:Admin|Super Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Package management
    Route::resource('packages', AdminPackageController::class)->except(['show']);
    Route::get('/packages/{package}', [AdminPackageController::class, 'show'])->name('packages.show');

    // Purchase management
    Route::get('/purchases', [AdminPurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/{purchase}', [AdminPurchaseController::class, 'show'])->name('purchases.show');
    Route::put('/purchases/{purchase}/status', [AdminPurchaseController::class, 'updateStatus'])->name('purchases.update-status');
    Route::get('/purchases/statistics', [AdminPurchaseController::class, 'statistics'])->name('purchases.statistics');
});

// Super Admin routes
Route::middleware(['auth', 'verified', 'role:Super Admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    // User management
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/promote', [UserController::class, 'promoteToAdmin'])->name('users.promote');
    Route::post('/users/{user}/demote', [UserController::class, 'demoteToUser'])->name('users.demote');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::get('/settings/system-info', [SettingsController::class, 'systemInfo'])->name('settings.system-info');
    Route::post('/settings/backup', [SettingsController::class, 'backupDatabase'])->name('settings.backup');
});

// Stripe webhook
Route::post('/stripe/webhook', [PurchaseController::class, 'webhook'])->name('stripe.webhook');

// Auth routes (generated by Laravel Breeze/Inertia)
require __DIR__ . '/settings.php';

// route login
