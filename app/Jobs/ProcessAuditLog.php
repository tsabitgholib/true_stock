<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAuditLog implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $data)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        AuditLog::create($this->data);
    }
}
