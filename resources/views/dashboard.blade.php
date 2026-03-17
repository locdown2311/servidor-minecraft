<x-app-layout>
    @section('title', 'Dashboard')

    <x-slot name="header">
        <h1 class="mc-page-title">Meus Servidores</h1>
    </x-slot>

    <div class="mc-container mc-section">
        <!-- Quick Actions -->
        <div class="mc-dashboard-header">
            <div>
                <p class="mc-text-muted">Gerencie seus servidores Minecraft</p>
            </div>
            <a href="{{ route('servers.create') }}" class="mc-btn mc-btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Novo Servidor
            </a>
        </div>

        @if($servers->isEmpty())
            <!-- Empty State -->
            <div class="mc-empty-state">
                <div class="mc-empty-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <path d="M8 21h8M12 17v4"/>
                    </svg>
                </div>
                <h3>Nenhum servidor ainda</h3>
                <p>Crie seu primeiro servidor Minecraft e comece a jogar com seus amigos!</p>
                <a href="{{ route('servers.create') }}" class="mc-btn mc-btn-primary mc-btn-lg">
                    Criar Primeiro Servidor
                </a>
            </div>
        @else
            <!-- Servers Grid -->
            <div class="mc-servers-grid">
                @foreach($servers as $server)
                    <div class="mc-server-card">
                        <div class="mc-server-card-header">
                            <div class="mc-server-info">
                                <h3 class="mc-server-name">{{ $server->name }}</h3>
                                <span class="mc-badge mc-badge-{{ $server->status_color }}">{{ $server->status_label }}</span>
                            </div>
                            <span class="mc-server-plan">{{ $server->plan->name }}</span>
                        </div>

                        <div class="mc-server-details">
                            <div class="mc-server-detail">
                                <span class="mc-server-detail-label">Endereço</span>
                                <span class="mc-server-detail-value">{{ $server->address }}</span>
                            </div>
                            <div class="mc-server-detail">
                                <span class="mc-server-detail-label">RAM</span>
                                <span class="mc-server-detail-value">{{ $server->plan->ram_label }}</span>
                            </div>
                            <div class="mc-server-detail">
                                <span class="mc-server-detail-label">Tipo</span>
                                <span class="mc-server-detail-value">{{ $server->server_type }}</span>
                            </div>
                            <div class="mc-server-detail">
                                <span class="mc-server-detail-label">Expira em</span>
                                <span class="mc-server-detail-value">{{ $server->expires_at?->diffForHumans() ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="mc-server-actions">
                            <a href="{{ route('servers.show', $server) }}" class="mc-btn mc-btn-outline mc-btn-sm">
                                Gerenciar
                            </a>
                            @if($server->isStopped())
                                <form method="POST" action="{{ route('servers.start', $server) }}">
                                    @csrf
                                    <button type="submit" class="mc-btn mc-btn-success mc-btn-sm">Iniciar</button>
                                </form>
                            @elseif($server->isRunning())
                                <form method="POST" action="{{ route('servers.stop', $server) }}">
                                    @csrf
                                    <button type="submit" class="mc-btn mc-btn-warning mc-btn-sm">Parar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
