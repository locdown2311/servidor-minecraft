<?php

namespace App\Jobs;

use App\Models\Server;
use App\Services\ServerProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DestroyServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Server $server
    ) {}

    public function handle(ServerProvisioningService $service): void
    {
        $service->destroy($this->server);
        $this->server->delete();
    }
}
