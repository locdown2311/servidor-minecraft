<div class="mc-panel-sidebar">
    <div class="mc-panel-sidebar-header">
        <div class="mc-panel-server-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        </div>
        <div>
            <h3 class="mc-panel-server-name">{{ $server->name }}</h3>
            <span class="mc-badge mc-badge-{{ $server->status_color }} mc-badge-sm">{{ $server->status_label }}</span>
        </div>
    </div>

    <nav class="mc-panel-nav">
        <a href="{{ route('servers.show', $server) }}"
           class="mc-panel-nav-item {{ request()->routeIs('servers.show') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            <span>Overview</span>
        </a>
        <a href="{{ route('servers.console', $server) }}"
           class="mc-panel-nav-item {{ request()->routeIs('servers.console') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
            <span>Console</span>
        </a>
        <a href="{{ route('servers.players', $server) }}"
           class="mc-panel-nav-item {{ request()->routeIs('servers.players') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            <span>Jogadores</span>
        </a>
        <a href="{{ route('servers.settings', $server) }}"
           class="mc-panel-nav-item {{ request()->routeIs('servers.settings') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            <span>Configurações</span>
        </a>
        <a href="{{ route('servers.files', $server) }}"
           class="mc-panel-nav-item {{ request()->routeIs('servers.files') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            <span>Arquivos</span>
        </a>
        <a href="{{ route('servers.backups', $server) }}"
           class="mc-panel-nav-item {{ request()->routeIs('servers.backups') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span>Backups</span>
        </a>
    </nav>

    {{-- Quick Actions --}}
    <div class="mc-panel-sidebar-actions">
        <h4 class="mc-panel-sidebar-section-title">Ações Rápidas</h4>
        @if($server->isStopped() || $server->status === 'error')
            <form method="POST" action="{{ route('servers.start', $server) }}">
                @csrf
                <button type="submit" class="mc-panel-action-btn mc-panel-action-start">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Iniciar
                </button>
            </form>
        @endif

        @if($server->isRunning())
            <form method="POST" action="{{ route('servers.stop', $server) }}">
                @csrf
                <button type="submit" class="mc-panel-action-btn mc-panel-action-stop">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                    Parar
                </button>
            </form>
            <form method="POST" action="{{ route('servers.restart', $server) }}">
                @csrf
                <button type="submit" class="mc-panel-action-btn mc-panel-action-restart">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                    Reiniciar
                </button>
            </form>
        @endif
    </div>

    {{-- Server Info --}}
    <div class="mc-panel-sidebar-info">
        <div class="mc-panel-sidebar-info-item">
            <span class="mc-panel-sidebar-info-label">Endereço</span>
            <span class="mc-panel-sidebar-info-value">{{ $server->address }}</span>
        </div>
        <div class="mc-panel-sidebar-info-item">
            <span class="mc-panel-sidebar-info-label">Plano</span>
            <span class="mc-panel-sidebar-info-value">{{ $server->plan->name }}</span>
        </div>
        <div class="mc-panel-sidebar-info-item">
            <span class="mc-panel-sidebar-info-label">Versão</span>
            <span class="mc-panel-sidebar-info-value">{{ $server->minecraft_version }}</span>
        </div>
        <div class="mc-panel-sidebar-info-item">
            <span class="mc-panel-sidebar-info-label">Tipo</span>
            <span class="mc-panel-sidebar-info-value">{{ $server->server_type }}</span>
        </div>
    </div>

    <a href="{{ route('dashboard') }}" class="mc-panel-back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Voltar ao Dashboard
    </a>
</div>
