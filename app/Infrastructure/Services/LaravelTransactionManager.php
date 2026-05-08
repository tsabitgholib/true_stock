<?php

namespace App\Infrastructure\Services;

use App\Domain\Inventory\Services\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManagerInterface
{
    public function begin(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollback(): void
    {
        DB::rollBack();
    }

    public function transactional(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
