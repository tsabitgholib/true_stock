<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use App\Domain\Inventory\Repositories\BaseRepositoryInterface;
use App\Infrastructure\Persistence\CompanyRepository;
use App\Infrastructure\Persistence\ItemRepository;
use App\Models\Company;
use App\Models\Item;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CompanyRepository::class, function ($app) {
            return new CompanyRepository(new Company());
        });
        $this->app->bind(ItemRepository::class, function ($app) {
            return new ItemRepository(new Item());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        \Illuminate\Support\Facades\Event::listen(
            \App\Domain\Inventory\Events\StockMovementOccurred::class,
            \App\Domain\Inventory\Listeners\RecordStockMovement::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Domain\Inventory\Events\StockMovementOccurred::class,
            \App\Domain\Inventory\Listeners\ClearDashboardCache::class
        );
    }
}
