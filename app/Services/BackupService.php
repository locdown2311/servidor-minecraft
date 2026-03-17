<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BackupService
{
    protected string $dockerHost;
    protected ?string $socketPath;
    protected string $backupDir = '/data/backups';

    public function __construct()
    {
        $this->socketPath = config('app.docker_socket', '/var/run/docker.sock');
        $this->dockerHost = rtrim(config('app.docker_host', 'http://localhost'), '/');
    }

    /**
     * Create a backup of the server world.
     */
    public function createBackup(Server $server, string $label = ''): bool
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $name = $label ? "{$label}_{$timestamp}" : "backup_{$timestamp}";
            $filename = "{$name}.tar.gz";

            // Ensure backup directory exists
            $this->execInContainer($server, ['mkdir', '-p', $this->backupDir]);

            // Create tarball of the world directory, excluding the backups dir itself
            $this->execInContainer($server, [
                'sh', '-c',
                "cd /data && tar -czf {$this->backupDir}/{$filename} --exclude='backups' --exclude='*.tar.gz' world/ server.properties *.json 2>/dev/null || true"
            ]);

            Log::info("Backup created for server #{$server->id}: {$filename}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to create backup for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * List available backups.
     */
    public function listBackups(Server $server): array
    {
        try {
            // Ensure backup directory exists
            $this->execInContainer($server, ['mkdir', '-p', $this->backupDir]);

            $output = $this->execInContainer($server, [
                'sh', '-c',
                "ls -la --time-style=long-iso {$this->backupDir}/*.tar.gz 2>/dev/null | tail -n +1"
            ]);

            return $this->parseBackupList($output);
        } catch (\Exception $e) {
            Log::error("Failed to list backups for server #{$server->id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete a backup.
     */
    public function deleteBackup(Server $server, string $filename): bool
    {
        try {
            if (!$this->isValidBackupName($filename)) {
                return false;
            }

            $this->execInContainer($server, ['rm', '-f', "{$this->backupDir}/{$filename}"]);
            Log::info("Backup deleted for server #{$server->id}: {$filename}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete backup {$filename} for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore a backup (extracts over current world).
     */
    public function restoreBackup(Server $server, string $filename): bool
    {
        try {
            if (!$this->isValidBackupName($filename)) {
                return false;
            }

            // Extract backup over /data
            $this->execInContainer($server, [
                'sh', '-c',
                "cd /data && tar -xzf {$this->backupDir}/{$filename}"
            ]);

            Log::info("Backup restored for server #{$server->id}: {$filename}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to restore backup {$filename} for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Download backup content (base64 encoded).
     */
    public function downloadBackup(Server $server, string $filename): ?string
    {
        try {
            if (!$this->isValidBackupName($filename)) {
                return null;
            }

            $output = $this->execInContainer($server, [
                'sh', '-c', "base64 {$this->backupDir}/{$filename}"
            ]);

            return base64_decode(trim($output));
        } catch (\Exception $e) {
            Log::error("Failed to download backup {$filename} for server #{$server->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate backup filename to prevent path traversal.
     */
    protected function isValidBackupName(string $filename): bool
    {
        return preg_match('/^[a-zA-Z0-9_\-]+\.tar\.gz$/', $filename) === 1;
    }

    /**
     * Parse ls output for backups.
     */
    protected function parseBackupList(string $output): array
    {
        $backups = [];
        $lines = array_filter(explode("\n", trim($output)));

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_contains($line, 'No such file')) continue;

            if (preg_match('/^[\-l]\S+\s+\d+\s+\S+\s+\S+\s+(\d+)\s+([\d\-]+)\s+([\d:]+)\s+.*\/(.+\.tar\.gz)$/', $line, $m)) {
                $backups[] = [
                    'name' => $m[4],
                    'size' => (int) $m[1],
                    'date' => $m[2] . ' ' . $m[3],
                ];
            }
        }

        // Most recent first
        usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));

        return $backups;
    }

    /**
     * Execute a command inside the Minecraft container.
     */
    protected function execInContainer(Server $server, array $cmd): string
    {
        $containerName = 'mc_' . $server->id;
        $request = $this->buildClient();
        $url = $this->dockerHost . '/v1.43';

        $createResponse = $request->post($url . "/containers/{$containerName}/exec", [
            'AttachStdout' => true,
            'AttachStderr' => true,
            'Cmd' => $cmd,
        ]);

        if (!$createResponse->successful()) {
            throw new \RuntimeException('Failed to create exec: ' . $createResponse->body());
        }

        $execId = $createResponse->json('Id');
        if (!$execId) {
            throw new \RuntimeException('No exec ID returned');
        }

        $startResponse = $this->buildClient()
            ->withBody(json_encode(['Detach' => false, 'Tty' => false]), 'application/json')
            ->post($url . "/exec/{$execId}/start");

        if ($startResponse->successful()) {
            return $this->stripDockerLogHeaders($startResponse->body());
        }

        throw new \RuntimeException('Exec start failed: ' . $startResponse->status());
    }

    /**
     * Build HTTP client with optional Unix socket.
     */
    protected function buildClient(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::timeout(60); // Longer timeout for backups

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
     * Strip Docker log stream 8-byte headers.
     */
    protected function stripDockerLogHeaders(string $raw): string
    {
        $lines = [];
        $offset = 0;
        $length = strlen($raw);

        while ($offset < $length) {
            if ($offset + 8 > $length) break;

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
