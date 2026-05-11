<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
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
            $lastRead = auth()->user()->last_read_notifications_at;
            
            $pendingAuditsQuery = \Modules\StockOpname\Models\StockOpname::where('status', 'pending');
            $lowStockQuery = \Modules\Product\Models\Product::whereRaw('stok <= min_stok')->where('stok', '>', 0);
            $unpaidBillsQuery = \Modules\TagihanSupplier\Models\TagihanSupplier::where('status', '!=', 'lunas');

            if ($lastRead) {
                $pendingAuditsQuery->where('created_at', '>', $lastRead);
                $lowStockQuery->where('updated_at', '>', $lastRead);
                $unpaidBillsQuery->where('created_at', '>', $lastRead);
            }

            $pendingAudits = $pendingAuditsQuery->count();
            $lowStock = $lowStockQuery->count();
            $unpaidBills = $unpaidBillsQuery->count();
            
            $view->with([
                'globalPendingAudits' => $pendingAudits,
                'globalLowStock' => $lowStock,
                'globalUnpaidBills' => $unpaidBills,
                'totalNotifications' => $pendingAudits + $lowStock + $unpaidBills
            ]);
        });

        \Illuminate\Pagination\Paginator::useTailwind();

        Event::listen(
            Login::class,
            UpdateLastLogin::class,
        );
    }
}
