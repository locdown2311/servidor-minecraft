<?php

namespace App\Jobs;

use App\Models\Server;
use App\Services\ServerProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Server $server
    ) {}

    public function handle(ServerProvisioningService $service): void
    {
        $service->provision($this->server);
    }

    public function failed(\Throwable $exception): void
    {
        $this->server->update(['status' => 'error']);
    }
}
