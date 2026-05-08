<?php

namespace App\Infrastructure\Persistence;

use App\Models\Item;

class ItemRepository extends EloquentBaseRepository
{
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }
}
