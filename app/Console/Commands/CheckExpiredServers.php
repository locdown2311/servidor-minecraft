<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\ServerProvisioningService;
use Illuminate\Console\Command;

class CheckExpiredServers extends Command
{
    protected $signature = 'servers:check-expired';
    protected $description = 'Stop servers that have expired';

    public function handle(ServerProvisioningService $service): int
    {
        $expired = Server::where('status', 'running')
            ->where('expires_at', '<', now())
            ->get();

        $this->info("Found {$expired->count()} expired server(s).");

        foreach ($expired as $server) {
            $this->info("Stopping server #{$server->id} ({$server->name})...");
            $service->stop($server);
            $server->update(['status' => 'expired']);
        }

        $this->info('Done.');
        return Command::SUCCESS;
    }
}
