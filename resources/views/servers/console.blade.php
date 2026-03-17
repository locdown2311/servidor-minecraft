<x-app-layout>
    @section('title', $server->name . ' — Console')

    <div class="mc-panel">
        @include('servers._sidebar')

        <div class="mc-panel-content">
            <div class="mc-panel-header">
                <h2 class="mc-panel-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
                    Console do Servidor
                </h2>
                @if($server->isRunning())
                    <span class="mc-console-status mc-console-status-online">
                        <span class="mc-pulse-dot"></span> Conectado
                    </span>
                @else
                    <span class="mc-console-status mc-console-status-offline">Desconectado</span>
                @endif
            </div>

            <div class="mc-console-container">
                <div class="mc-console-output" id="console-output">
                    @if($server->isRunning())
                        <pre id="console-logs">{{ $logs ?: 'Aguardando logs do servidor...' }}</pre>
                    @else
                        <div class="mc-console-offline">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                            <p>O servidor precisa estar <strong>online</strong> para acessar o console.</p>
                            <form method="POST" action="{{ route('servers.start', $server) }}">
                                @csrf
                                <button type="submit" class="mc-btn mc-btn-success">Iniciar Servidor</button>
                            </form>
                        </div>
                    @endif
                </div>

                @if($server->isRunning())
                    <form id="console-form" class="mc-console-input-wrapper" onsubmit="return sendConsoleCommand(event)">
                        <span class="mc-console-prompt">&gt;</span>
                        <input type="text" id="console-input" class="mc-console-input"
                               placeholder="Digite um comando..." autocomplete="off" autofocus>
                        <button type="submit" class="mc-console-send-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </form>
                @endif
            </div>

            @if($server->isRunning())
                <div class="mc-console-help">
                    <p><strong>Dica:</strong> Use comandos do Minecraft como <code>say</code>, <code>gamemode</code>, <code>tp</code>, <code>give</code>, <code>list</code>, <code>op</code>, etc.</p>
                </div>
            @endif
        </div>
    </div>

    @if($server->isRunning())
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const logsUrl = '{{ route("servers.logs", $server) }}';
        const commandUrl = '{{ route("servers.command", $server) }}';
        const consoleOutput = document.getElementById('console-output');
        const consoleLogs = document.getElementById('console-logs');
        const commandHistory = [];
        let historyIndex = -1;

        // Auto-refresh logs every 3 seconds
        setInterval(fetchLogs, 3000);

        function fetchLogs() {
            fetch(logsUrl)
                .then(r => r.json())
                .then(data => {
                    if (data.logs && consoleLogs) {
                        consoleLogs.textContent = data.logs;
                        consoleOutput.scrollTop = consoleOutput.scrollHeight;
                    }
                })
                .catch(() => {});
        }

        function sendConsoleCommand(e) {
            e.preventDefault();
            const input = document.getElementById('console-input');
            const command = input.value.trim();
            if (!command) return false;

            // Add to history
            commandHistory.unshift(command);
            historyIndex = -1;

            // Append command to console
            consoleLogs.textContent += '\n> ' + command + '\n';
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
            input.value = '';

            fetch(commandUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ command: command }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.output) {
                    consoleLogs.textContent += data.output + '\n';
                    consoleOutput.scrollTop = consoleOutput.scrollHeight;
                }
            })
            .catch(err => {
                consoleLogs.textContent += 'Erro ao enviar comando.\n';
            });

            return false;
        }

        // Command history with arrow keys
        document.getElementById('console-input').addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp' && commandHistory.length > 0) {
                e.preventDefault();
                historyIndex = Math.min(historyIndex + 1, commandHistory.length - 1);
                this.value = commandHistory[historyIndex];
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                historyIndex = Math.max(historyIndex - 1, -1);
                this.value = historyIndex >= 0 ? commandHistory[historyIndex] : '';
            }
        });

        // Auto-scroll to bottom on load
        consoleOutput.scrollTop = consoleOutput.scrollHeight;
    </script>
    @endif
</x-app-layout>
