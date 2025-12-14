<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function __construct()
    {
        // Only Super Admin can access these methods
            if (!auth()->user()->hasRole('Super Admin')) {
                abort(403, 'Only Super Admin can access system settings.');
            }
    }

    /**
     * Display system settings.
     */
    public function index()
    {
        $settings = [
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
            ],
            'mail' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
            'stripe' => [
                'key' => config('services.stripe.key') ? '***' . substr(config('services.stripe.key'), -4) : null,
                'secret' => config('services.stripe.secret') ? '***' . substr(config('services.stripe.secret'), -4) : null,
                'webhook_secret' => config('services.stripe.webhook_secret') ? '***' . substr(config('services.stripe.webhook_secret'), -4) : null,
            ],
            'storage' => [
                'default' => config('filesystems.default'),
                'max_upload_size' => ini_get('upload_max_filesize'),
                'images_path' => 'storage/package-images/',
            ],
            'cache' => [
                'driver' => config('cache.default'),
            ],
        ];

        return Inertia::render('SuperAdmin/Settings/Index', [
            'settings' => $settings,
            'disk_usage' => $this->getDiskUsage(),
        ]);
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
            'weekday_days' => 'array',
            'weekend_days' => 'array',
        ]);

        // Update .env file or database settings
        // Note: In production, you should use a database settings table
        // or a package like spatie/laravel-settings

        // For now, we'll update a cache/database table
        $settings = [
            'app_name' => $validated['app_name'],
            'app_url' => $validated['app_url'],
            'mail_from_address' => $validated['mail_from_address'],
            'mail_from_name' => $validated['mail_from_name'],
            'weekday_days' => $validated['weekday_days'] ?? [1, 2, 3, 4], // Mon-Thu
            'weekend_days' => $validated['weekend_days'] ?? [5, 6, 0], // Fri-Sun
        ];

        // Store in cache (temporary solution)
        Cache::forever('system_settings', $settings);

        // If stripe credentials are provided, update them
        if (!empty($validated['stripe_key'])) {
            // In real application, you would update .env file securely
            // or use a secure storage method
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');

        return back()->with('success', 'Application cache cleared successfully.');
    }

    /**
     * Get system information.
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'server_os' => php_uname(),
            'database_driver' => config('database.default'),
            'database_name' => config('database.connections.' . config('database.default') . '.database'),
            'timezone' => config('app.timezone'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];

        return Inertia::render('SuperAdmin/Settings/SystemInfo', [
            'info' => $info,
        ]);
    }

    /**
     * Get disk usage statistics.
     */
    private function getDiskUsage()
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Backup database.
     */
    public function backupDatabase()
    {
        \Artisan::call('backup:run', [
            '--only-db' => true,
        ]);

        return back()->with('success', 'Database backup created successfully.');
    }
}