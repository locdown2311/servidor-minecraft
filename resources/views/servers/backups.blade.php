<x-app-layout>
    @section('title', $server->name . ' — Backups')

    <div class="mc-panel">
        @include('servers._sidebar')

        <div class="mc-panel-content">
            <div class="mc-panel-header">
                <h2 class="mc-panel-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Backups
                </h2>
            </div>

            @if(!$server->isRunning())
                <div class="mc-panel-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <h3>Servidor Offline</h3>
                    <p>Inicie o servidor para gerenciar backups.</p>
                </div>
            @else
                {{-- Create Backup Form --}}
                <div class="mc-panel-card" style="margin-bottom: 1.5rem;">
                    <div class="mc-panel-card-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        Criar Novo Backup
                    </div>
                    <div class="mc-panel-card-body">
                        <form method="POST" action="{{ route('servers.backups.create', $server) }}" class="mc-backup-create-form">
                            @csrf
                            <input type="text" name="label" class="mc-input mc-input-sm" placeholder="Rótulo do backup (opcional, ex: antes-do-update)">
                            <button type="submit" class="mc-btn mc-btn-primary mc-btn-sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Criar Backup
                            </button>
                        </form>
                        <p class="mc-text-muted" style="margin-top: 0.75rem; font-size: 0.8rem;">O backup incluirá o mundo, server.properties e arquivos JSON de configuração.</p>
                    </div>
                </div>

                {{-- Backup List --}}
                <div class="mc-panel-card">
                    <div class="mc-panel-card-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                        Backups Disponíveis ({{ count($backups) }})
                    </div>
                    <div class="mc-panel-card-body">
                        @if(empty($backups))
                            <p class="mc-text-muted" style="text-align: center; padding: 2rem 0;">Nenhum backup encontrado. Crie um acima para começar.</p>
                        @else
                            <div class="mc-backup-list">
                                @foreach($backups as $backup)
                                    <div class="mc-backup-item">
                                        <div class="mc-backup-info">
                                            <div class="mc-backup-icon">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            </div>
                                            <div>
                                                <span class="mc-backup-name">{{ $backup['name'] }}</span>
                                                <span class="mc-backup-meta">{{ formatBytes($backup['size']) }} · {{ $backup['date'] }}</span>
                                            </div>
                                        </div>
                                        <div class="mc-backup-actions">
                                            <form method="POST" action="{{ route('servers.backups.restore', $server) }}" onsubmit="return confirm('Restaurar este backup? Os dados atuais do mundo serão substituídos.')">
                                                @csrf
                                                <input type="hidden" name="name" value="{{ $backup['name'] }}">
                                                <button type="submit" class="mc-btn mc-btn-outline mc-btn-xs" title="Restaurar">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                                                    Restaurar
                                                </button>
                                            </form>
                                            <a href="{{ route('servers.backups.download', ['server' => $server, 'name' => $backup['name']]) }}" class="mc-btn mc-btn-ghost mc-btn-xs" title="Download">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            </a>
                                            <form method="POST" action="{{ route('servers.backups.delete', $server) }}" onsubmit="return confirm('Excluir este backup?')">
                                                @csrf
                                                <input type="hidden" name="name" value="{{ $backup['name'] }}">
                                                <button type="submit" class="mc-btn mc-btn-danger-ghost mc-btn-xs" title="Excluir">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mc-console-help" style="margin-top: 1rem;">
                    <p><strong>Dica:</strong> Ao restaurar um backup, o servidor deve ser <strong>reiniciado</strong> para aplicar as mudanças. Faça backup antes de restaurar para evitar perda de dados.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
