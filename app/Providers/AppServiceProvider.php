<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Events\Login;
use App\Listeners\UpdateLastLogin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (!auth()->check()) return;

            $userId  = auth()->id();
            $lastRead = auth()->user()->last_read_notifications_at;
            // Cache key berbeda per user, expire 2 menit
            $cacheKey = "notif_counts_{$userId}_" . ($lastRead ? $lastRead->timestamp : 'never');

            $data = Cache::remember($cacheKey, 120, function () use ($lastRead) {
                $pendingAuditsQuery = \Modules\StockOpname\Models\StockOpname::where('status', 'pending');
                $lowStockQuery      = \Modules\Product\Models\Product::whereRaw('stok <= min_stok')->where('stok', '>', 0);
                $unpaidBillsQuery   = \Modules\TagihanSupplier\Models\TagihanSupplier::where('status', '!=', 'lunas');

                if ($lastRead) {
                    $pendingAuditsQuery->where('created_at', '>', $lastRead);
                    $lowStockQuery->where('updated_at', '>', $lastRead);
                    $unpaidBillsQuery->where('created_at', '>', $lastRead);
                }

                return [
                    'pendingAudits' => $pendingAuditsQuery->count(),
                    'lowStock'      => $lowStockQuery->count(),
                    'unpaidBills'   => $unpaidBillsQuery->count(),
                ];
            });

            $view->with([
                'globalPendingAudits' => $data['pendingAudits'],
                'globalLowStock'      => $data['lowStock'],
                'globalUnpaidBills'   => $data['unpaidBills'],
                'totalNotifications'  => $data['pendingAudits'] + $data['lowStock'] + $data['unpaidBills'],
            ]);
        });

        \Illuminate\Pagination\Paginator::useTailwind();

        Event::listen(
            Login::class,
            UpdateLastLogin::class,
        );
    }
}
