<x-app-layout>
    @section('title', 'Admin — Servidores')

    <x-slot name="header">
        <h1 class="mc-page-title">Todos os Servidores</h1>
    </x-slot>

    <div class="mc-container mc-section">
        <div class="mc-card">
            <div class="mc-table-wrapper">
                <table class="mc-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>Plano</th>
                            <th>Status</th>
                            <th>Porta MC</th>
                            <th>Porta FTP</th>
                            <th>Expira em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servers as $server)
                            <tr>
                                <td>#{{ $server->id }}</td>
                                <td>{{ $server->name }}</td>
                                <td>{{ $server->user->name }}</td>
                                <td>{{ $server->plan->name }}</td>
                                <td><span class="mc-badge mc-badge-{{ $server->status_color }}">{{ $server->status_label }}</span></td>
                                <td>{{ $server->port ?? '—' }}</td>
                                <td>{{ $server->ftp_port ?? '—' }}</td>
                                <td>{{ $server->expires_at?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('servers.show', $server) }}" class="mc-btn mc-btn-ghost mc-btn-sm">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mc-pagination">
                {{ $servers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
