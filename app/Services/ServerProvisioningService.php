<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServerProvisioningService
{
    protected string $dockerHost;
    protected ?string $socketPath;
    protected string $hostDataPath;
    protected string $containerDataPath;
    protected string $apiVersion;

    public function __construct()
    {
        // Em Linux, usa o socket Unix do Docker diretamente (mais seguro)
        // Monte o socket no container: -v /var/run/docker.sock:/var/run/docker.sock
        $this->socketPath = config('app.docker_socket', '/var/run/docker.sock');
        $this->dockerHost = rtrim(config('app.docker_host', 'http://localhost'), '/');
        
        // Caminho no HOST onde os dados ficarão (/mnt/docker_data/servidor_mine)
        $this->hostDataPath = rtrim(config('app.minecraft_host_data_path', '/mnt/docker_data/servidor_mine'), '/');
        
        // Caminho INTERNO no container Minecraft (itzg/minecraft-server usa /data)
        $this->containerDataPath = config('app.minecraft_container_path', '/data');
        $this->apiVersion = rtrim(config('app.docker_api_version', 'v1.40'), '/');
    }

    /**
     * Provision a new Minecraft server + FTP sidecar container.
     */
    public function provision(Server $server): bool
    {
        try {
            $server->update(['status' => 'provisioning']);

            $mcPort = $this->findFreePort(25565, 26565);
            $ftpPort = $this->findFreePort(21000, 22000);
            $ftpUser = 'mc_' . $server->id;
            $ftpPass = Str::random(16);
            $volumeName = 'mc_data_' . $server->id;

            // 1. Ensure images are present
            $this->pullImage('itzg/minecraft-server');
            $this->pullImage('fauria/vsftpd');

            // 2. Prepare host path for the server

            // 2. Create Minecraft container
            $mcResponse = $this->dockerApi('POST', '/containers/create', [
                'Image' => 'itzg/minecraft-server',
                'name' => 'mc_' . $server->id,
                'Env' => [
                    'EULA=TRUE',
                    'MEMORY=' . $server->plan->ram_mb . 'M',
                    'TYPE=' . $server->server_type,
                    'VERSION=' . $server->minecraft_version,
                    'MOTD=' . ($server->motd ?? 'Servidor Minecraft'),
                    'MAX_PLAYERS=' . $server->plan->max_players,
                ],
                'HostConfig' => [
                    'PortBindings' => [
                        '25565/tcp' => [
                            ['HostPort' => (string) $mcPort],
                        ],
                    ],
                    'Binds' => [
                        $serverHostPath . ':' . $this->containerDataPath,
                    ],
                    'RestartPolicy' => [
                        'Name' => 'unless-stopped',
                    ],
                ],
            ], ['name' => 'mc_' . $server->id]);

            $mcContainerId = $mcResponse['Id'] ?? null;

            // Start MC container
            $this->dockerApi('POST', "/containers/mc_{$server->id}/start");

            // 3. Create FTP container
            $ftpResponse = $this->dockerApi('POST', '/containers/create', [
                'Image' => 'fauria/vsftpd',
                'Env' => [
                    'FTP_USER=' . $ftpUser,
                    'FTP_PASS=' . $ftpPass,
                    'PASV_MIN_PORT=21100',
                    'PASV_MAX_PORT=21110',
                    'PASV_ADDRESS=' . config('app.server_ip', '127.0.0.1'),
                ],
                'HostConfig' => [
                    'PortBindings' => [
                        '21/tcp' => [
                            ['HostPort' => (string) $ftpPort],
                        ],
                        '21100-21110/tcp' => [
                            ['HostPort' => '21100-21110'],
                        ],
                    ],
                    'Binds' => [
                        $serverHostPath . ':/home/vsftpd/' . $ftpUser,
                    ],
                    'RestartPolicy' => [
                        'Name' => 'unless-stopped',
                    ],
                ],
            ], ['name' => 'ftp_' . $server->id]);

            $ftpContainerId = $ftpResponse['Id'] ?? null;

            // Start FTP container
            $this->dockerApi('POST', "/containers/ftp_{$server->id}/start");

            $server->update([
                'status' => 'running',
                'port' => $mcPort,
                'ftp_port' => $ftpPort,
                'ftp_user' => $ftpUser,
                'ftp_password' => $ftpPass,
                'container_id' => $mcContainerId,
                'ftp_container_id' => $ftpContainerId,
            ]);

            Log::info("Server #{$server->id} provisioned via Docker API", [
                'mc_port' => $mcPort,
                'ftp_port' => $ftpPort,
            ]);

            return true;
        } catch (\Exception $e) {
            $server->update(['status' => 'error']);
            Log::error("Failed to provision server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pull a Docker image from a registry.
     */
    protected function pullImage(string $image): void
    {
        try {
            Log::info("Pulling Docker image: {$image}");
            $this->dockerApi('POST', '/images/create', null, ['fromImage' => $image]);
        } catch (\Exception $e) {
            Log::warning("Failed to pull image {$image}: " . $e->getMessage());
        }
    }

    /**
     * Start a stopped server.
     */
    public function start(Server $server): bool
    {
        try {
            $this->dockerApi('POST', "/containers/mc_{$server->id}/start");
            $this->dockerApi('POST', "/containers/ftp_{$server->id}/start");
            $server->update(['status' => 'running']);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to start server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Stop a running server.
     */
    public function stop(Server $server): bool
    {
        try {
            $this->dockerApi('POST', "/containers/mc_{$server->id}/stop");
            $this->dockerApi('POST', "/containers/ftp_{$server->id}/stop");
            $server->update(['status' => 'stopped']);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to stop server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restart a server.
     */
    public function restart(Server $server): bool
    {
        try {
            $this->dockerApi('POST', "/containers/mc_{$server->id}/restart");
            $this->dockerApi('POST', "/containers/ftp_{$server->id}/restart");
            $server->update(['status' => 'running']);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to restart server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Destroy a server's containers and volumes.
     */
    public function destroy(Server $server): bool
    {
        try {
            // Stop containers (ignore 304 "already stopped" and 404 "not found")
            $this->dockerApi('POST', "/containers/mc_{$server->id}/stop", ignoreErrors: true);
            $this->dockerApi('POST', "/containers/ftp_{$server->id}/stop", ignoreErrors: true);

            // Remove containers
            $this->dockerApi('DELETE', "/containers/mc_{$server->id}", query: ['force' => 'true'], ignoreErrors: true);
            $this->dockerApi('DELETE', "/containers/ftp_{$server->id}", query: ['force' => 'true'], ignoreErrors: true);

            // Remove volume (not used anymore, we use host binds)
            // $this->dockerApi('DELETE', "/volumes/mc_data_{$server->id}", ignoreErrors: true);
            
            // Note: Manual cleanup of $serverHostPath might be needed here if desired,
            // but usually we keep it for data persistence unless explicitly asked.

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to destroy server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get server logs.
     */
    public function getLogs(Server $server, int $lines = 100): string
    {
        try {
            $url = $this->dockerHost . '/v1.43' . "/containers/mc_{$server->id}/logs?stdout=true&stderr=true&tail={$lines}";

            $request = Http::timeout(5);

            if ($this->socketPath && file_exists($this->socketPath)) {
                $request = $request->withOptions([
                    'curl' => [
                        CURLOPT_UNIX_SOCKET_PATH => $this->socketPath,
                    ],
                ]);
            }

            $response = $request->get($url);

            if ($response->successful()) {
                return $this->stripDockerLogHeaders($response->body());
            }

            return 'Não foi possível obter os logs.';
        } catch (\Exception $e) {
            return 'Erro ao obter logs: ' . $e->getMessage();
        }
    }

    /**
     * Send a command to the Minecraft server via Docker exec.
     */
    public function sendCommand(Server $server, string $command): string
    {
        try {
            $containerName = 'mc_' . $server->id;

            // Create exec instance
            $execCreate = $this->dockerApi('POST', "/containers/{$containerName}/exec", [
                'AttachStdout' => true,
                'AttachStderr' => true,
                'Cmd' => ['rcon-cli', $command],
            ]);

            $execId = $execCreate['Id'] ?? null;
            if (!$execId) {
                return 'Erro: Não foi possível criar instância exec.';
            }

            // Start exec and get output
            $url = $this->dockerHost . '/v1.43' . "/exec/{$execId}/start";
            $response = $this->buildClient()
                ->withBody(json_encode(['Detach' => false, 'Tty' => false]), 'application/json')
                ->post($url);

            if ($response->successful()) {
                $output = $this->stripDockerLogHeaders($response->body());
                return trim($output) ?: 'Comando executado com sucesso.';
            }

            return 'Erro ao executar comando.';
        } catch (\Exception $e) {
            Log::error("Failed to send command to server #{$server->id}: " . $e->getMessage());
            return 'Erro: ' . $e->getMessage();
        }
    }

    /**
     * Get container resource usage via Docker Stats API.
     */
    public function getContainerStats(Server $server): array
    {
        try {
            $containerName = 'mc_' . $server->id;
            $url = $this->dockerHost . '/v1.43' . "/containers/{$containerName}/stats?stream=false";

            $response = $this->buildClient()->timeout(5)->get($url);

            if (!$response->successful()) {
                return $this->defaultStats();
            }

            $stats = $response->json();

            // Calculate CPU percentage
            $cpuDelta = ($stats['cpu_stats']['cpu_usage']['total_usage'] ?? 0) -
                        ($stats['precpu_stats']['cpu_usage']['total_usage'] ?? 0);
            $systemDelta = ($stats['cpu_stats']['system_cpu_usage'] ?? 0) -
                           ($stats['precpu_stats']['system_cpu_usage'] ?? 0);
            $numCpus = $stats['cpu_stats']['online_cpus'] ?? 1;

            $cpuPercent = 0;
            if ($systemDelta > 0 && $cpuDelta > 0) {
                $cpuPercent = round(($cpuDelta / $systemDelta) * $numCpus * 100, 1);
            }

            // Memory usage
            $memUsage = $stats['memory_stats']['usage'] ?? 0;
            $memLimit = $stats['memory_stats']['limit'] ?? 0;
            $memCache = $stats['memory_stats']['stats']['cache'] ?? 0;
            $memUsed = $memUsage - $memCache;
            $memPercent = $memLimit > 0 ? round(($memUsed / $memLimit) * 100, 1) : 0;

            // Network I/O
            $netRx = 0;
            $netTx = 0;
            foreach ($stats['networks'] ?? [] as $net) {
                $netRx += $net['rx_bytes'] ?? 0;
                $netTx += $net['tx_bytes'] ?? 0;
            }

            return [
                'cpu_percent' => min($cpuPercent, 100),
                'memory_used' => $memUsed,
                'memory_limit' => $memLimit,
                'memory_percent' => min($memPercent, 100),
                'network_rx' => $netRx,
                'network_tx' => $netTx,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get stats for server #{$server->id}: " . $e->getMessage());
            return $this->defaultStats();
        }
    }

    /**
     * Get default stats array.
     */
    protected function defaultStats(): array
    {
        return [
            'cpu_percent' => 0,
            'memory_used' => 0,
            'memory_limit' => 0,
            'memory_percent' => 0,
            'network_rx' => 0,
            'network_tx' => 0,
        ];
    }

    /**
     * Read server.properties from the Minecraft container.
     */
    public function getServerProperties(Server $server): array
    {
        try {
            $output = $this->execInContainer($server, ['cat', $this->containerDataPath . '/server.properties']);
            $properties = [];

            foreach (explode("\n", $output) as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $properties[trim($parts[0])] = trim($parts[1]);
                }
            }

            return $properties;
        } catch (\Exception $e) {
            Log::error("Failed to read server.properties for #{$server->id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save server.properties to the Minecraft container.
     */
    public function saveServerProperties(Server $server, array $properties): bool
    {
        try {
            // Build properties file content
            $content = "#Minecraft server properties\n#" . date('r') . "\n";
            foreach ($properties as $key => $value) {
                $content .= "{$key}={$value}\n";
            }

            // Write using sh -c with heredoc
            $escaped = str_replace("'", "'\\''", $content);
            $this->execInContainer($server, ['sh', '-c', "printf '%s' '{$escaped}' > {$this->containerDataPath}/server.properties"]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to save server.properties for #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the list of online players.
     */
    public function getPlayerList(Server $server): array
    {
        try {
            $output = $this->sendCommand($server, 'list');

            // Parse output like "There are 2 of max 20 players online: Player1, Player2"
            if (preg_match('/:\s*(.+)$/', $output, $matches)) {
                $playerNames = array_map('trim', explode(',', $matches[1]));
                return array_filter($playerNames, fn($name) => !empty($name));
            }

            // Alternative format: "There are 0 of max 20 players online."
            return [];
        } catch (\Exception $e) {
            Log::error("Failed to get player list for #{$server->id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kick a player from the server.
     */
    public function kickPlayer(Server $server, string $player, string $reason = ''): string
    {
        $cmd = $reason ? "kick {$player} {$reason}" : "kick {$player}";
        return $this->sendCommand($server, $cmd);
    }

    /**
     * Ban a player from the server.
     */
    public function banPlayer(Server $server, string $player, string $reason = ''): string
    {
        $cmd = $reason ? "ban {$player} {$reason}" : "ban {$player}";
        return $this->sendCommand($server, $cmd);
    }

    /**
     * Unban a player from the server.
     */
    public function unbanPlayer(Server $server, string $player): string
    {
        return $this->sendCommand($server, "pardon {$player}");
    }

    /**
     * Give operator status to a player.
     */
    public function opPlayer(Server $server, string $player): string
    {
        return $this->sendCommand($server, "op {$player}");
    }

    /**
     * Remove operator status from a player.
     */
    public function deOpPlayer(Server $server, string $player): string
    {
        return $this->sendCommand($server, "deop {$player}");
    }

    /**
     * Add player to the whitelist.
     */
    public function whitelistAdd(Server $server, string $player): string
    {
        return $this->sendCommand($server, "whitelist add {$player}");
    }

    /**
     * Remove player from the whitelist.
     */
    public function whitelistRemove(Server $server, string $player): string
    {
        return $this->sendCommand($server, "whitelist remove {$player}");
    }

    /**
     * Execute a command inside the container and return output.
     */
    protected function execInContainer(Server $server, array $cmd): string
    {
        $containerName = 'mc_' . $server->id;

        $execCreate = $this->dockerApi('POST', "/containers/{$containerName}/exec", [
            'AttachStdout' => true,
            'AttachStderr' => true,
            'Cmd' => $cmd,
        ]);

        $execId = $execCreate['Id'] ?? null;
        if (!$execId) {
            throw new \RuntimeException('Failed to create exec instance');
        }

        $url = $this->dockerHost . '/' . $this->apiVersion . "/exec/{$execId}/start";
        $response = $this->buildClient()
            ->withBody(json_encode(['Detach' => false, 'Tty' => false]), 'application/json')
            ->post($url);

        if ($response->successful()) {
            return $this->stripDockerLogHeaders($response->body());
        }

        throw new \RuntimeException('Exec failed with status: ' . $response->status());
    }

    /**
     * Find a free port in the given range.
     */
    protected function findFreePort(int $min, int $max): int
    {
        $usedPorts = Server::whereNotNull('port')
            ->whereIn('status', ['running', 'provisioning', 'stopped'])
            ->pluck('port')
            ->merge(
                Server::whereNotNull('ftp_port')
                    ->whereIn('status', ['running', 'provisioning', 'stopped'])
                    ->pluck('ftp_port')
            )
            ->toArray();

        for ($port = $min; $port <= $max; $port++) {
            if (!in_array($port, $usedPorts)) {
                return $port;
            }
        }

        throw new \RuntimeException("No free ports available in range {$min}-{$max}");
    }

    /**
     * Build an HTTP client, optionally using the Docker Unix socket.
     */
    protected function buildClient(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::timeout(30);

        // Se o socket Unix existe, usar CURLOPT_UNIX_SOCKET_PATH
        // para comunicar diretamente com o Docker daemon (Linux)
        if ($this->socketPath && file_exists($this->socketPath)) {
            $request = $request->withOptions([
                'curl' => [
                    CURLOPT_UNIX_SOCKET_PATH => $this->socketPath,
                ],
            ]);
        }

        return $request;
    }

    /**
     * Make a request to the Docker Engine API.
     */
    protected function dockerApi(
        string $method,
        string $endpoint,
        ?array $body = null,
        ?array $query = null,
        bool $ignoreErrors = false
    ): ?array {
        $url = $this->dockerHost . '/' . $this->apiVersion . $endpoint;

        $request = $this->buildClient();

        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $response = match (strtoupper($method)) {
            'GET' => $request->get($url),
            'POST' => $body ? $request->post($url, $body) : $request->post($url),
            'DELETE' => $request->delete($url),
            default => throw new \RuntimeException("Unsupported HTTP method: {$method}"),
        };

        if (!$response->successful() && !$ignoreErrors) {
            $error = $response->json('message') ?? $response->body();
            throw new \RuntimeException("Docker API error ({$response->status()}): {$error}");
        }

        $contentType = $response->header('Content-Type');
        if ($contentType && str_contains($contentType, 'application/json')) {
            return $response->json();
        }

        return null;
    }

    /**
     * Strip Docker log stream 8-byte headers.
     */
    protected function stripDockerLogHeaders(string $raw): string
    {
        $lines = [];
        $offset = 0;
        $length = strlen($raw);

        while ($offset < $length) {
            if ($offset + 8 > $length) {
                break;
            }

            $header = unpack('C1type/C3padding/N1size', substr($raw, $offset, 8));
            $size = $header['size'] ?? 0;
            $offset += 8;

            if ($offset + $size > $length) {
                $lines[] = substr($raw, $offset);
                break;
            }

            $lines[] = substr($raw, $offset, $size);
            $offset += $size;
        }

        return implode('', $lines);
    }
}
