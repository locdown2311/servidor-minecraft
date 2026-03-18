<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FileManagerService
{
    protected string $dockerHost;
    protected ?string $socketPath;
    protected string $basePath;
    protected string $apiVersion;

    public function __construct()
    {
        $this->socketPath = config('app.docker_socket', '/var/run/docker.sock');
        $this->dockerHost = rtrim(config('app.docker_host', 'http://localhost'), '/');
        $this->basePath = rtrim(config('app.minecraft_data_path', '/mnt/docker_data/servidor_mine'), '/');
        $this->apiVersion = rtrim(config('app.docker_api_version', 'v1.40'), '/');
    }

    /**
     * List files and directories in a given path.
     */
    public function listFiles(Server $server, string $path = ''): array
    {
        try {
            $fullPath = $this->resolvePath($path);
            $output = $this->execInContainer($server, [
                'sh', '-c',
                "ls -la --time-style=long-iso {$fullPath} 2>/dev/null | tail -n +2"
            ]);

            return $this->parseLsOutput($output, $path);
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to list {$path} for server #{$server->id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Read a file's contents.
     */
    public function readFile(Server $server, string $path): ?string
    {
        try {
            $fullPath = $this->resolvePath($path);
            $size = $this->getFileSize($server, $path);

            // Limit to 1MB for safety
            if ($size > 1048576) {
                return null;
            }

            return $this->execInContainer($server, ['cat', $fullPath]);
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to read {$path} for server #{$server->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Write content to a file.
     */
    public function writeFile(Server $server, string $path, string $content): bool
    {
        try {
            $fullPath = $this->resolvePath($path);

            // Use base64 to safely transfer content
            $encoded = base64_encode($content);
            $this->execInContainer($server, [
                'sh', '-c', "echo '{$encoded}' | base64 -d > {$fullPath}"
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to write {$path} for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a file or directory.
     */
    public function deleteFile(Server $server, string $path): bool
    {
        try {
            if (empty($path) || $path === '/' || $path === '.') {
                return false; // Safety: never delete root
            }

            $fullPath = $this->resolvePath($path);
            $this->execInContainer($server, ['rm', '-rf', $fullPath]);

            return true;
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to delete {$path} for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a directory.
     */
    public function createFolder(Server $server, string $path): bool
    {
        try {
            $fullPath = $this->resolvePath($path);
            $this->execInContainer($server, ['mkdir', '-p', $fullPath]);
            return true;
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to mkdir {$path} for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create an empty file.
     */
    public function createFile(Server $server, string $path): bool
    {
        try {
            $fullPath = $this->resolvePath($path);
            $this->execInContainer($server, ['touch', $fullPath]);
            return true;
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to create {$path} for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rename/move a file.
     */
    public function renameFile(Server $server, string $from, string $to): bool
    {
        try {
            $fromPath = $this->resolvePath($from);
            $toPath = $this->resolvePath($to);
            $this->execInContainer($server, ['mv', $fromPath, $toPath]);
            return true;
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to rename {$from} to {$to} for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file size in bytes.
     */
    public function getFileSize(Server $server, string $path): int
    {
        try {
            $fullPath = $this->resolvePath($path);
            $output = trim($this->execInContainer($server, [
                'sh', '-c', "stat -c %s {$fullPath} 2>/dev/null || echo 0"
            ]));
            return (int) $output;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Upload file content to the container using Docker exec with tar.
     */
    public function uploadFile(Server $server, string $path, string $content): bool
    {
        try {
            $fullPath = $this->resolvePath($path);
            $encoded = base64_encode($content);

            $this->execInContainer($server, [
                'sh', '-c', "echo '{$encoded}' | base64 -d > {$fullPath}"
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to upload {$path} for server #{$server->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file content for download.
     */
    public function downloadFile(Server $server, string $path): ?string
    {
        try {
            $fullPath = $this->resolvePath($path);

            // Use base64 encode in container, decode on our side
            $output = $this->execInContainer($server, [
                'sh', '-c', "base64 {$fullPath}"
            ]);

            return base64_decode(trim($output));
        } catch (\Exception $e) {
            Log::error("FileManager: Failed to download {$path} for server #{$server->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a path is a directory.
     */
    public function isDirectory(Server $server, string $path): bool
    {
        try {
            $fullPath = $this->resolvePath($path);
            $output = trim($this->execInContainer($server, [
                'sh', '-c', "[ -d {$fullPath} ] && echo 1 || echo 0"
            ]));
            return $output === '1';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Resolve path safely within the base data directory.
     */
    protected function resolvePath(string $path): string
    {
        // Sanitize path to prevent directory traversal
        $path = str_replace(['..', "\0"], '', $path);
        $path = ltrim($path, '/');

        return $this->basePath . ($path ? '/' . $path : '');
    }

    /**
     * Parse `ls -la` output into structured array.
     */
    protected function parseLsOutput(string $output, string $currentPath): array
    {
        $files = [];
        $lines = array_filter(explode("\n", trim($output)));

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Parse ls -la output:
            // drwxr-xr-x 2 root root 4096 2024-03-17 12:34 dirname
            // -rw-r--r-- 1 root root 1234 2024-03-17 12:34 filename.txt
            if (preg_match('/^([d\-l])([rwx\-]{9})\s+\d+\s+\S+\s+\S+\s+(\d+)\s+([\d\-]+)\s+([\d:]+)\s+(.+)$/', $line, $m)) {
                $name = trim($m[6]);
                if ($name === '.' || $name === '..') continue;

                $isDir = $m[1] === 'd';
                $size = (int) $m[3];
                $date = $m[4] . ' ' . $m[5];

                $files[] = [
                    'name' => $name,
                    'path' => $currentPath ? $currentPath . '/' . $name : $name,
                    'is_dir' => $isDir,
                    'size' => $size,
                    'modified' => $date,
                    'permissions' => $m[1] . $m[2],
                    'editable' => !$isDir && $size < 1048576 && $this->isTextFile($name),
                ];
            }
        }

        // Sort: directories first, then alphabetically
        usort($files, function ($a, $b) {
            if ($a['is_dir'] !== $b['is_dir']) {
                return $a['is_dir'] ? -1 : 1;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $files;
    }

    /**
     * Check if a file is likely a text file based on extension.
     */
    protected function isTextFile(string $name): bool
    {
        $textExtensions = [
            'txt', 'log', 'properties', 'yml', 'yaml', 'json', 'cfg',
            'conf', 'ini', 'toml', 'xml', 'html', 'css', 'js', 'java',
            'sh', 'bat', 'cmd', 'md', 'csv', 'mcmeta', 'lang', 'sk',
        ];

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        return in_array($ext, $textExtensions);
    }

    /**
     * Execute a command inside the Minecraft container.
     */
    protected function execInContainer(Server $server, array $cmd): string
    {
        $containerName = 'mc_' . $server->id;

        $request = $this->buildClient();
        $url = $this->dockerHost . '/' . $this->apiVersion;

        // Create exec
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

        // Start exec
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
        $request = Http::timeout(30);

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
