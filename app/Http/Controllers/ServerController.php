<?php

namespace App\Http\Controllers;

use App\Jobs\DestroyServerJob;
use App\Jobs\ProvisionServerJob;
use App\Models\Plan;
use App\Models\Server;
use App\Services\BackupService;
use App\Services\FileManagerService;
use App\Services\ServerProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function __construct(
        protected ServerProvisioningService $provisioning,
        protected FileManagerService $fileManager,
        protected BackupService $backupService,
    ) {}

    public function create(): View
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('servers.create', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'minecraft_version' => 'required|string|max:20',
            'server_type' => 'required|in:VANILLA,PAPER,FORGE,FABRIC,SPIGOT,BUKKIT',
            'motd' => 'nullable|string|max:255',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $server = Server::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'name' => $validated['name'],
            'minecraft_version' => $validated['minecraft_version'],
            'server_type' => $validated['server_type'],
            'motd' => $validated['motd'] ?? 'Um servidor Minecraft',
            'status' => 'pending',
            'expires_at' => now()->addMonth(),
        ]);

        // Dispatch provisioning job
        ProvisionServerJob::dispatch($server);

        return redirect()->route('servers.show', $server)
            ->with('success', 'Servidor criado! Provisionamento em andamento...');
    }

    /**
     * Server overview (main panel page).
     */
    public function show(Server $server): View
    {
        $this->authorize('view', $server);

        // Sync status from Docker to fix stale states (error, provisioning, etc.)
        $this->provisioning->syncStatus($server);
        $server->refresh();

        $stats = $this->defaultStats();
        if ($server->isRunning() && $server->container_id) {
            $stats = $this->provisioning->getContainerStats($server);
        }

        return view('servers.show', compact('server', 'stats'));
    }

    /**
     * Console page (interactive terminal).
     */
    public function console(Server $server): View
    {
        $this->authorize('view', $server);

        $logs = '';
        if ($server->isRunning() && $server->container_id) {
            $logs = $this->provisioning->getLogs($server, 100);
        }

        return view('servers.console', compact('server', 'logs'));
    }

    /**
     * AJAX: Fetch latest logs.
     */
    public function logs(Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $logs = '';
        if ($server->isRunning() && $server->container_id) {
            $logs = $this->provisioning->getLogs($server, 100);
        }

        return response()->json([
            'logs' => $logs,
            'status' => $server->fresh()->status,
        ]);
    }

    /**
     * AJAX: Send a command to the server console.
     */
    public function sendCommand(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);

        $request->validate(['command' => 'required|string|max:500']);

        if (!$server->isRunning()) {
            return response()->json(['output' => 'Servidor não está online.'], 422);
        }

        $output = $this->provisioning->sendCommand($server, $request->command);

        return response()->json(['output' => $output]);
    }

    /**
     * AJAX: Get container resource usage.
     */
    public function resources(Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        if (!$server->isRunning()) {
            return response()->json($this->defaultStats());
        }

        $stats = $this->provisioning->getContainerStats($server);
        return response()->json($stats);
    }

    /**
     * Players management page.
     */
    public function players(Server $server): View
    {
        $this->authorize('view', $server);

        $players = [];
        if ($server->isRunning() && $server->container_id) {
            $players = $this->provisioning->getPlayerList($server);
        }

        return view('servers.players', compact('server', 'players'));
    }

    /**
     * AJAX: Perform player action (kick, ban, op, deop, whitelist).
     */
    public function playerAction(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);

        $request->validate([
            'player' => 'required|string|max:50',
            'action' => 'required|in:kick,ban,unban,op,deop,whitelist-add,whitelist-remove',
            'reason' => 'nullable|string|max:255',
        ]);

        if (!$server->isRunning()) {
            return response()->json(['output' => 'Servidor não está online.'], 422);
        }

        $player = $request->player;
        $reason = $request->reason ?? '';

        $output = match ($request->action) {
            'kick' => $this->provisioning->kickPlayer($server, $player, $reason),
            'ban' => $this->provisioning->banPlayer($server, $player, $reason),
            'unban' => $this->provisioning->unbanPlayer($server, $player),
            'op' => $this->provisioning->opPlayer($server, $player),
            'deop' => $this->provisioning->deOpPlayer($server, $player),
            'whitelist-add' => $this->provisioning->whitelistAdd($server, $player),
            'whitelist-remove' => $this->provisioning->whitelistRemove($server, $player),
        };

        return response()->json(['output' => $output]);
    }

    /**
     * Server settings page (server.properties editor).
     */
    public function settings(Server $server): View
    {
        $this->authorize('view', $server);

        $properties = [];
        if ($server->isRunning() && $server->container_id) {
            $properties = $this->provisioning->getServerProperties($server);
        }

        return view('servers.settings', compact('server', 'properties'));
    }

    /**
     * Save server.properties.
     */
    public function saveSettings(Request $request, Server $server): RedirectResponse
    {
        $this->authorize('update', $server);

        if (!$server->isRunning()) {
            return back()->with('error', 'Servidor precisa estar online para salvar configurações.');
        }

        $properties = $request->input('properties', []);

        $success = $this->provisioning->saveServerProperties($server, $properties);

        if ($success) {
            return back()->with('success', 'Configurações salvas! Reinicie o servidor para aplicar.');
        }

        return back()->with('error', 'Erro ao salvar configurações.');
    }

    // =============================================
    // FILE MANAGER
    // =============================================

    /**
     * File manager page.
     */
    public function files(Request $request, Server $server): View
    {
        $this->authorize('view', $server);

        $path = $request->query('path', '');
        $files = [];
        $breadcrumbs = $this->buildBreadcrumbs($path);

        if ($server->isRunning() && $server->container_id) {
            $files = $this->fileManager->listFiles($server, $path);
        }

        return view('servers.files', compact('server', 'files', 'path', 'breadcrumbs'));
    }

    /**
     * AJAX: List files in a directory.
     */
    public function filesList(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $path = $request->query('path', '');

        if (!$server->isRunning()) {
            return response()->json(['files' => [], 'error' => 'Servidor offline.'], 422);
        }

        $files = $this->fileManager->listFiles($server, $path);
        return response()->json(['files' => $files, 'path' => $path]);
    }

    /**
     * AJAX: Read file content.
     */
    public function fileRead(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);
        $request->validate(['path' => 'required|string']);

        $content = $this->fileManager->readFile($server, $request->path);

        if ($content === null) {
            return response()->json(['error' => 'Arquivo muito grande ou não legível.'], 422);
        }

        return response()->json(['content' => $content, 'path' => $request->path]);
    }

    /**
     * AJAX: Save file content.
     */
    public function fileSave(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);
        $request->validate([
            'path' => 'required|string',
            'content' => 'required|string',
        ]);

        $success = $this->fileManager->writeFile($server, $request->path, $request->content);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Arquivo salvo.' : 'Erro ao salvar.',
        ]);
    }

    /**
     * AJAX: Delete file or folder.
     */
    public function fileDelete(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);
        $request->validate(['path' => 'required|string']);

        $success = $this->fileManager->deleteFile($server, $request->path);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Excluído.' : 'Erro ao excluir.',
        ]);
    }

    /**
     * AJAX: Create file or folder.
     */
    public function fileCreate(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);
        $request->validate([
            'path' => 'required|string',
            'type' => 'required|in:file,folder',
        ]);

        $success = $request->type === 'folder'
            ? $this->fileManager->createFolder($server, $request->path)
            : $this->fileManager->createFile($server, $request->path);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Criado.' : 'Erro ao criar.',
        ]);
    }

    /**
     * AJAX: Rename file or folder.
     */
    public function fileRename(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);
        $request->validate([
            'from' => 'required|string',
            'to' => 'required|string',
        ]);

        $success = $this->fileManager->renameFile($server, $request->from, $request->to);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Renomeado.' : 'Erro ao renomear.',
        ]);
    }

    /**
     * Upload file to server.
     */
    public function fileUpload(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'path' => 'required|string',
        ]);

        $file = $request->file('file');
        $targetPath = rtrim($request->path, '/') . '/' . $file->getClientOriginalName();
        $content = file_get_contents($file->getRealPath());

        $success = $this->fileManager->uploadFile($server, $targetPath, $content);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Upload concluído.' : 'Erro no upload.',
        ]);
    }

    /**
     * Download file from server.
     */
    public function fileDownload(Request $request, Server $server): Response|JsonResponse
    {
        $this->authorize('view', $server);
        $path = $request->query('path', '');

        if (empty($path)) {
            return response()->json(['error' => 'Path obrigatório.'], 422);
        }

        $content = $this->fileManager->downloadFile($server, $path);

        if ($content === null) {
            return response()->json(['error' => 'Erro ao baixar arquivo.'], 422);
        }

        $filename = basename($path);

        return response($content)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Build breadcrumb segments from a path.
     */
    protected function buildBreadcrumbs(string $path): array
    {
        $breadcrumbs = [['name' => 'raiz', 'path' => '']];

        if (empty($path)) return $breadcrumbs;

        $segments = explode('/', $path);
        $accumulated = '';

        foreach ($segments as $segment) {
            if (empty($segment)) continue;
            $accumulated .= ($accumulated ? '/' : '') . $segment;
            $breadcrumbs[] = ['name' => $segment, 'path' => $accumulated];
        }

        return $breadcrumbs;
    }

    // =============================================
    // BACKUPS
    // =============================================

    /**
     * Backups page.
     */
    public function backups(Server $server): View
    {
        $this->authorize('view', $server);

        $backups = [];
        if ($server->isRunning() && $server->container_id) {
            $backups = $this->backupService->listBackups($server);
        }

        return view('servers.backups', compact('server', 'backups'));
    }

    /**
     * Create a backup.
     */
    public function backupCreate(Request $request, Server $server): RedirectResponse
    {
        $this->authorize('update', $server);

        if (!$server->isRunning()) {
            return back()->with('error', 'Servidor precisa estar online.');
        }

        $label = $request->input('label', '');
        $success = $this->backupService->createBackup($server, $label);

        return back()->with(
            $success ? 'success' : 'error',
            $success ? 'Backup criado com sucesso!' : 'Erro ao criar backup.'
        );
    }

    /**
     * Delete a backup.
     */
    public function backupDelete(Request $request, Server $server): RedirectResponse
    {
        $this->authorize('update', $server);
        $request->validate(['name' => 'required|string']);

        $success = $this->backupService->deleteBackup($server, $request->name);

        return back()->with(
            $success ? 'success' : 'error',
            $success ? 'Backup excluído.' : 'Erro ao excluir backup.'
        );
    }

    /**
     * Restore a backup.
     */
    public function backupRestore(Request $request, Server $server): RedirectResponse
    {
        $this->authorize('update', $server);
        $request->validate(['name' => 'required|string']);

        $success = $this->backupService->restoreBackup($server, $request->name);

        return back()->with(
            $success ? 'success' : 'error',
            $success ? 'Backup restaurado! Reinicie o servidor para aplicar.' : 'Erro ao restaurar backup.'
        );
    }

    /**
     * Download a backup.
     */
    public function backupDownload(Request $request, Server $server): Response|JsonResponse
    {
        $this->authorize('view', $server);
        $name = $request->query('name', '');

        if (empty($name)) {
            return response()->json(['error' => 'Nome obrigatório.'], 422);
        }

        $content = $this->backupService->downloadBackup($server, $name);

        if ($content === null) {
            return response()->json(['error' => 'Erro ao baixar backup.'], 422);
        }

        return response($content)
            ->header('Content-Type', 'application/gzip')
            ->header('Content-Disposition', "attachment; filename=\"{$name}\"");
    }

    // =============================================
    // SERVER LIFECYCLE
    // =============================================

    public function start(Server $server): RedirectResponse
    {
        $this->authorize('update', $server);

        if ($server->isExpired()) {
            return back()->with('error', 'Servidor expirado. Renove para continuar.');
        }

        $this->provisioning->start($server);
        return back()->with('success', 'Servidor iniciado!');
    }

    public function stop(Server $server): RedirectResponse
    {
        $this->authorize('update', $server);
        $this->provisioning->stop($server);
        return back()->with('success', 'Servidor parado.');
    }

    public function restart(Server $server): RedirectResponse
    {
        $this->authorize('update', $server);
        $this->provisioning->restart($server);
        return back()->with('success', 'Servidor reiniciado!');
    }

    public function destroy(Server $server): RedirectResponse
    {
        $this->authorize('delete', $server);

        DestroyServerJob::dispatch($server);

        return redirect()->route('dashboard')
            ->with('success', 'Servidor sendo removido...');
    }

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
}
