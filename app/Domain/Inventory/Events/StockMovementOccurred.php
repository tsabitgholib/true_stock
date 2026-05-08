<?php

namespace App\Domain\Inventory\Events;

use App\Application\Inventory\DTOs\StockMovementData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockMovementOccurred
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public StockMovementData $data
    ) {}
}
