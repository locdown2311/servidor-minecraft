<x-app-layout>
    @section('title', 'Painel Admin')

    <x-slot name="header">
        <h1 class="mc-page-title">Painel Administrativo</h1>
    </x-slot>

    <div class="mc-container mc-section">
        <!-- Stats Cards -->
        <div class="mc-stats-grid">
            <div class="mc-stat-card">
                <div class="mc-stat-icon" style="--accent: #6c5ce7;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
                <div class="mc-stat-info">
                    <span class="mc-stat-value">{{ $stats['users'] }}</span>
                    <span class="mc-stat-label">Usuários</span>
                </div>
            </div>
            <div class="mc-stat-card">
                <div class="mc-stat-icon" style="--accent: #00b894;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                </div>
                <div class="mc-stat-info">
                    <span class="mc-stat-value">{{ $stats['servers'] }}</span>
                    <span class="mc-stat-label">Servidores Total</span>
                </div>
            </div>
            <div class="mc-stat-card">
                <div class="mc-stat-icon" style="--accent: #00cec9;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                </div>
                <div class="mc-stat-info">
                    <span class="mc-stat-value">{{ $stats['servers_running'] }}</span>
                    <span class="mc-stat-label">Online Agora</span>
                </div>
            </div>
            <div class="mc-stat-card">
                <div class="mc-stat-icon" style="--accent: #fdcb6e;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                </div>
                <div class="mc-stat-info">
                    <span class="mc-stat-value">R$ {{ number_format($stats['revenue'], 2, ',', '.') }}</span>
                    <span class="mc-stat-label">Receita Total</span>
                </div>
            </div>
        </div>

        <!-- Admin Navigation -->
        <div class="mc-admin-nav">
            <a href="{{ route('admin.servers') }}" class="mc-btn mc-btn-outline">Ver Todos os Servidores</a>
            <a href="{{ route('admin.users') }}" class="mc-btn mc-btn-outline">Ver Todos os Usuários</a>
            <a href="{{ route('admin.plans') }}" class="mc-btn mc-btn-outline">Gerenciar Planos</a>
        </div>

        <!-- Recent Servers -->
        <div class="mc-card">
            <h3 class="mc-card-title">Servidores Recentes</h3>
            <div class="mc-table-wrapper">
                <table class="mc-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>Plano</th>
                            <th>Status</th>
                            <th>Porta</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentServers as $server)
                            <tr>
                                <td>#{{ $server->id }}</td>
                                <td><a href="{{ route('servers.show', $server) }}" class="mc-link">{{ $server->name }}</a></td>
                                <td>{{ $server->user->name }}</td>
                                <td>{{ $server->plan->name }}</td>
                                <td><span class="mc-badge mc-badge-{{ $server->status_color }}">{{ $server->status_label }}</span></td>
                                <td>{{ $server->port ?? '—' }}</td>
                                <td>{{ $server->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
