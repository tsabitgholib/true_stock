<?php

namespace App\Application\Inventory\DTOs;

readonly class StockMovementData
{
    public function __construct(
        public int $item_id,
        public int $warehouse_id,
        public float $quantity,
        public string $movement_type,
        public ?int $location_id = null,
        public ?int $rack_id = null,
        public ?int $batch_id = null,
        public ?string $reference_type = null,
        public ?int $reference_id = null,
        public ?string $remarks = null,
        public ?int $user_id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            item_id: $data['item_id'],
            warehouse_id: $data['warehouse_id'],
            quantity: (float) $data['quantity'],
            movement_type: $data['movement_type'] ?? 'STOCK_IN',
            location_id: $data['location_id'] ?? null,
            rack_id: $data['rack_id'] ?? null,
            batch_id: $data['batch_id'] ?? null,
            reference_type: $data['reference_type'] ?? null,
            reference_id: $data['reference_id'] ?? null,
            remarks: $data['remarks'] ?? null,
            user_id: $data['user_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id,
            'location_id' => $this->location_id,
            'rack_id' => $this->rack_id,
            'batch_id' => $this->batch_id,
            'quantity' => $this->quantity,
            'movement_type' => $this->movement_type,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'remarks' => $this->remarks,
            'user_id' => $this->user_id,
        ];
    }
}
