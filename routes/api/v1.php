<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Inventory Routes
    Route::get('/inventory', [\App\Interfaces\Http\Controllers\InventoryController::class, 'index']);
    
    // Master Data Routes
    Route::get('/items', [\App\Interfaces\Http\Controllers\ItemController::class, 'index']);
});
