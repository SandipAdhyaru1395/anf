<?php

namespace App\Jobs;

use App\Services\Planufac\PlanufacClient;
use App\Services\Planufac\PlanufacProductSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPlanufacProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900; // 15 minutes

    public function handle(): void
    {
        $client = new PlanufacClient();
        $service = new PlanufacProductSyncService($client);
        $service->syncAll(200);
    }
}

