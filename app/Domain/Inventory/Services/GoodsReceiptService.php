<?php

namespace App\Domain\Inventory\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\StockBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GoodsReceiptService
{
    private StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function completeGRN(int $grnId): void
    {
        DB::transaction(function () use ($grnId) {
            $grn = GoodsReceipt::with('details')->findOrFail($grnId);
            
            if ($grn->status === 'COMPLETED') {
                throw new \Exception("GRN already completed.");
            }

            foreach ($grn->details as $detail) {
                $this->stockService->stockIn([
                    'item_id' => $detail->item_id,
                    'warehouse_id' => $grn->warehouse_id,
                    'location_id' => $detail->location_id,
                    'rack_id' => $detail->rack_id,
                    'batch_id' => $detail->batch_id,
                    'quantity' => $detail->quantity,
                    'reference_type' => 'GRN',
                    'reference_id' => $grn->id,
                    'remarks' => "Received from GRN #{$grn->grn_number}",
                ]);
            }

            $grn->update(['status' => 'COMPLETED']);
        });
    }
}
