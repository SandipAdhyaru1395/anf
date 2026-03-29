<?php

namespace App\Jobs;

use App\Services\Planufac\PlanufacInventoryLevelSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPlanufacInventoryLevelsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, mixed> */
    public array $backoff = [10, 60, 120];

    public int $timeout = 120;

    /**
     * @param  array<int, mixed>  $inventoryLevels
     */
    public function __construct(
        public array $inventoryLevels,
        public ?string $requestId = null,
    ) {
    }

    public function handle(PlanufacInventoryLevelSyncService $service): void
    {
        $service->applyLevelsWithLogging($this->inventoryLevels, $this->requestId);
    }
}
