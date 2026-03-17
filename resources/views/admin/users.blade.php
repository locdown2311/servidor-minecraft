<x-app-layout>
    @section('title', 'Admin — Usuários')

    <x-slot name="header">
        <h1 class="mc-page-title">Todos os Usuários</h1>
    </x-slot>

    <div class="mc-container mc-section">
        <div class="mc-card">
            <div class="mc-table-wrapper">
                <table class="mc-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Servidores</th>
                            <th>Admin</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>#{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->servers_count }}</td>
                                <td>
                                    @if($user->is_admin)
                                        <span class="mc-badge mc-badge-green">Admin</span>
                                    @else
                                        <span class="mc-badge mc-badge-gray">Usuário</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mc-pagination">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
