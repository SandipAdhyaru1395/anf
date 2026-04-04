<?php

namespace App\Jobs;

use App\Services\Planufac\PlanufacClient;
use App\Services\Planufac\PlanufacProductSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPlanufacProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900; // 15 minutes

    public function __construct(public ?string $brandIdCsv = null)
    {
    }

    public function handle(): void
    {
        try {
            $client = new PlanufacClient();
            $service = new PlanufacProductSyncService($client);
            $service->syncAll(1000, $this->brandIdCsv);
        } catch (\Throwable $e) {
            Log::error('Planufac product sync job failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}

