<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Services\InventoryAnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private InventoryAnalyticsService $analyticsService;

    public function __construct(InventoryAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(): Response
    {
        $kpis = $this->analyticsService->getDashboardKPIs();
        $valuationByWarehouse = $this->analyticsService->getValuationByWarehouse();

        return Inertia::render('Dashboard', [
            'kpis' => $kpis,
            'valuationByWarehouse' => $valuationByWarehouse,
        ]);
    }
}
