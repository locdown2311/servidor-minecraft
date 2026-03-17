<x-app-layout>
    @section('title', 'Admin — Planos')

    <x-slot name="header">
        <h1 class="mc-page-title">Gerenciar Planos</h1>
    </x-slot>

    <div class="mc-container mc-section">
        <div class="mc-card">
            <div class="mc-table-wrapper">
                <table class="mc-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>RAM</th>
                            <th>Max Jogadores</th>
                            <th>Preço Mensal</th>
                            <th>Servidores Ativos</th>
                            <th>Ativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <td>#{{ $plan->id }}</td>
                                <td>{{ $plan->name }}</td>
                                <td>{{ $plan->ram_label }}</td>
                                <td>{{ $plan->max_players }}</td>
                                <td>R$ {{ number_format($plan->price_monthly, 2, ',', '.') }}</td>
                                <td>{{ $plan->servers_count }}</td>
                                <td>
                                    @if($plan->is_active)
                                        <span class="mc-badge mc-badge-green">Ativo</span>
                                    @else
                                        <span class="mc-badge mc-badge-gray">Inativo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
