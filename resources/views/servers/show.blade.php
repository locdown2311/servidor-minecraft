<x-app-layout>
    @section('title', $server->name . ' — Overview')

    <div class="mc-panel">
        @include('servers._sidebar')

        <div class="mc-panel-content">
            <div class="mc-panel-header">
                <h2 class="mc-panel-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Overview
                </h2>
            </div>

            {{-- Resource Monitoring Cards --}}
            <div class="mc-resource-grid" id="resource-grid" data-server-id="{{ $server->id }}">
                <div class="mc-resource-card">
                    <div class="mc-resource-icon mc-resource-cpu">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg>
                    </div>
                    <div class="mc-resource-info">
                        <span class="mc-resource-label">CPU</span>
                        <span class="mc-resource-value" id="cpu-value">{{ $stats['cpu_percent'] }}%</span>
                    </div>
                    <div class="mc-resource-bar">
                        <div class="mc-resource-bar-fill mc-resource-bar-cpu" id="cpu-bar" style="width: {{ $stats['cpu_percent'] }}%"></div>
                    </div>
                </div>

                <div class="mc-resource-card">
                    <div class="mc-resource-icon mc-resource-ram">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 19v-14a2 2 0 012-2h8a2 2 0 012 2v14"/><path d="M2 19h20"/><rect x="8" y="7" width="8" height="4" rx="1"/></svg>
                    </div>
                    <div class="mc-resource-info">
                        <span class="mc-resource-label">RAM</span>
                        <span class="mc-resource-value" id="ram-value">{{ formatBytes($stats['memory_used']) }} / {{ formatBytes($stats['memory_limit']) }}</span>
                    </div>
                    <div class="mc-resource-bar">
                        <div class="mc-resource-bar-fill mc-resource-bar-ram" id="ram-bar" style="width: {{ $stats['memory_percent'] }}%"></div>
                    </div>
                </div>

                <div class="mc-resource-card">
                    <div class="mc-resource-icon mc-resource-net">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <div class="mc-resource-info">
                        <span class="mc-resource-label">Rede</span>
                        <span class="mc-resource-value" id="net-value">↓ {{ formatBytes($stats['network_rx']) }} / ↑ {{ formatBytes($stats['network_tx']) }}</span>
                    </div>
                </div>

                <div class="mc-resource-card">
                    <div class="mc-resource-icon mc-resource-status">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                    </div>
                    <div class="mc-resource-info">
                        <span class="mc-resource-label">Status</span>
                        <span class="mc-resource-value mc-resource-status-{{ $server->status_color }}">{{ $server->status_label }}</span>
                    </div>
                </div>
            </div>

            {{-- Info Cards Grid --}}
            <div class="mc-panel-info-grid">
                {{-- Connection Info --}}
                <div class="mc-panel-card">
                    <h3 class="mc-panel-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                        Conexão
                    </h3>
                    <div class="mc-panel-card-body">
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Endereço</span>
                            <span class="mc-panel-detail-value mc-copyable" onclick="copyText('{{ $server->address }}', this)">
                                <code>{{ $server->address }}</code>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                            </span>
                        </div>
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Versão</span>
                            <span class="mc-panel-detail-value">{{ $server->minecraft_version }}</span>
                        </div>
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Tipo</span>
                            <span class="mc-panel-detail-value">{{ $server->server_type }}</span>
                        </div>
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">MOTD</span>
                            <span class="mc-panel-detail-value">{{ $server->motd ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Plan Info --}}
                <div class="mc-panel-card">
                    <h3 class="mc-panel-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                        Plano — {{ $server->plan->name }}
                    </h3>
                    <div class="mc-panel-card-body">
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">RAM</span>
                            <span class="mc-panel-detail-value">{{ $server->plan->ram_label }}</span>
                        </div>
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Max Jogadores</span>
                            <span class="mc-panel-detail-value">{{ $server->plan->max_players }}</span>
                        </div>
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Preço</span>
                            <span class="mc-panel-detail-value">R$ {{ number_format($server->plan->price_monthly, 2, ',', '.') }}/mês</span>
                        </div>
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Expira em</span>
                            <span class="mc-panel-detail-value {{ $server->isExpired() ? 'mc-text-danger' : '' }}">
                                {{ $server->expires_at?->format('d/m/Y H:i') ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- FTP Info --}}
                <div class="mc-panel-card">
                    <h3 class="mc-panel-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                        Acesso FTP
                    </h3>
                    <div class="mc-panel-card-body">
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Host</span>
                            <span class="mc-panel-detail-value mc-copyable" onclick="copyText('{{ $server->ftp_address }}', this)">
                                <code>{{ $server->ftp_address }}</code>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                            </span>
                        </div>
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Usuário</span>
                            <span class="mc-panel-detail-value mc-copyable" onclick="copyText('{{ $server->ftp_user }}', this)">
                                <code>{{ $server->ftp_user ?? 'N/A' }}</code>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                            </span>
                        </div>
                        <div class="mc-panel-detail">
                            <span class="mc-panel-detail-label">Senha</span>
                            <span class="mc-panel-detail-value mc-copyable" onclick="copyText('{{ $server->ftp_password }}', this)">
                                <code>{{ $server->ftp_password ? '••••••••' : 'N/A' }}</code>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="mc-panel-card mc-panel-card-danger">
                    <h3 class="mc-panel-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Zona de Perigo
                    </h3>
                    <div class="mc-panel-card-body">
                        <p class="mc-text-muted" style="margin-bottom: 1rem;">Excluir o servidor apagará todos os dados permanentemente.</p>
                        <form method="POST" action="{{ route('servers.destroy', $server) }}" onsubmit="return confirm('Tem certeza? Esta ação é irreversível!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="mc-btn mc-btn-danger">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                Excluir Servidor
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($server->isRunning())
    <script>
        // Auto-refresh resource stats every 10 seconds
        setInterval(function() {
            fetch('{{ route("servers.resources", $server) }}')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('cpu-value').textContent = data.cpu_percent + '%';
                    document.getElementById('cpu-bar').style.width = data.cpu_percent + '%';

                    const ramUsed = formatBytesJS(data.memory_used);
                    const ramLimit = formatBytesJS(data.memory_limit);
                    document.getElementById('ram-value').textContent = ramUsed + ' / ' + ramLimit;
                    document.getElementById('ram-bar').style.width = data.memory_percent + '%';

                    const netRx = formatBytesJS(data.network_rx);
                    const netTx = formatBytesJS(data.network_tx);
                    document.getElementById('net-value').textContent = '↓ ' + netRx + ' / ↑ ' + netTx;
                })
                .catch(() => {});
        }, 10000);

        function formatBytesJS(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }
    </script>
    @endif

    <script>
        function copyText(text, el) {
            navigator.clipboard.writeText(text).then(() => {
                const code = el.querySelector('code');
                const original = code.textContent;
                code.textContent = 'Copiado!';
                setTimeout(() => { code.textContent = original; }, 1500);
            });
        }
    </script>
</x-app-layout>
