<?php

namespace App\Domain\Inventory\Listeners;

use App\Domain\Inventory\Events\StockMovementOccurred;
use Illuminate\Support\Facades\Cache;

class ClearDashboardCache
{
    /**
     * Handle the event.
     */
    public function handle(StockMovementOccurred $event): void
    {
        Cache::forget('dashboard_kpis');
    }
}
