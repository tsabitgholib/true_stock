<?php

namespace App\Domain\Inventory\Services;

interface TransactionManagerInterface
{
    public function begin(): void;
    public function commit(): void;
    public function rollback(): void;
    public function transactional(callable $callback): mixed;
}
