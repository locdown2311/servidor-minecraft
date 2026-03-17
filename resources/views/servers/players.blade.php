<x-app-layout>
    @section('title', $server->name . ' — Jogadores')

    <div class="mc-panel">
        @include('servers._sidebar')

        <div class="mc-panel-content">
            <div class="mc-panel-header">
                <h2 class="mc-panel-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    Jogadores
                </h2>
                @if($server->isRunning())
                    <button onclick="refreshPlayerList()" class="mc-btn mc-btn-ghost mc-btn-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                        Atualizar
                    </button>
                @endif
            </div>

            @if(!$server->isRunning())
                <div class="mc-panel-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <h3>Servidor Offline</h3>
                    <p>Inicie o servidor para ver os jogadores online.</p>
                </div>
            @else
                {{-- Online Players --}}
                <div class="mc-panel-card">
                    <h3 class="mc-panel-card-title">
                        <span class="mc-pulse-dot mc-pulse-dot-green"></span>
                        Jogadores Online ({{ count($players) }}/{{ $server->plan->max_players }})
                    </h3>
                    <div class="mc-panel-card-body">
                        @if(empty($players))
                            <p class="mc-text-muted">Nenhum jogador online no momento.</p>
                        @else
                            <div class="mc-players-list" id="players-list">
                                @foreach($players as $player)
                                    <div class="mc-player-row">
                                        <div class="mc-player-info">
                                            <img src="https://mc-heads.net/avatar/{{ $player }}/32" alt="{{ $player }}" class="mc-player-avatar">
                                            <span class="mc-player-name">{{ $player }}</span>
                                        </div>
                                        <div class="mc-player-actions">
                                            <button onclick="playerAction('op', '{{ $player }}')" class="mc-btn mc-btn-ghost mc-btn-xs" title="Dar OP">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                            </button>
                                            <button onclick="playerAction('kick', '{{ $player }}')" class="mc-btn mc-btn-warning-ghost mc-btn-xs" title="Kick">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                            </button>
                                            <button onclick="playerAction('ban', '{{ $player }}')" class="mc-btn mc-btn-danger-ghost mc-btn-xs" title="Ban">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="mc-panel-card">
                    <h3 class="mc-panel-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        Ações de Jogador
                    </h3>
                    <div class="mc-panel-card-body">
                        <div class="mc-player-action-form">
                            <input type="text" id="player-name-input" class="mc-input" placeholder="Nome do jogador">
                            <div class="mc-player-action-buttons">
                                <button onclick="playerActionFromInput('op')" class="mc-btn mc-btn-outline mc-btn-sm">OP</button>
                                <button onclick="playerActionFromInput('deop')" class="mc-btn mc-btn-outline mc-btn-sm">De-OP</button>
                                <button onclick="playerActionFromInput('kick')" class="mc-btn mc-btn-warning mc-btn-sm">Kick</button>
                                <button onclick="playerActionFromInput('ban')" class="mc-btn mc-btn-danger mc-btn-sm">Ban</button>
                                <button onclick="playerActionFromInput('unban')" class="mc-btn mc-btn-outline mc-btn-sm">Unban</button>
                                <button onclick="playerActionFromInput('whitelist-add')" class="mc-btn mc-btn-success mc-btn-sm">Whitelist +</button>
                                <button onclick="playerActionFromInput('whitelist-remove')" class="mc-btn mc-btn-outline mc-btn-sm">Whitelist −</button>
                            </div>
                        </div>
                        <div id="player-action-result" class="mc-player-action-result" style="display: none;"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($server->isRunning())
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const playerActionUrl = '{{ route("servers.player-action", $server) }}';

        function playerAction(action, player) {
            if (action === 'ban' && !confirm('Banir ' + player + '?')) return;
            if (action === 'kick' && !confirm('Kickar ' + player + '?')) return;

            doPlayerAction(action, player);
        }

        function playerActionFromInput(action) {
            const input = document.getElementById('player-name-input');
            const player = input.value.trim();
            if (!player) { input.focus(); return; }

            doPlayerAction(action, player);
            input.value = '';
        }

        function doPlayerAction(action, player) {
            const resultDiv = document.getElementById('player-action-result');

            fetch(playerActionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ action: action, player: player }),
            })
            .then(r => r.json())
            .then(data => {
                resultDiv.style.display = 'block';
                resultDiv.className = 'mc-player-action-result mc-player-action-success';
                resultDiv.textContent = data.output || 'Ação executada.';
                setTimeout(() => { resultDiv.style.display = 'none'; }, 5000);
            })
            .catch(() => {
                resultDiv.style.display = 'block';
                resultDiv.className = 'mc-player-action-result mc-player-action-error';
                resultDiv.textContent = 'Erro ao executar ação.';
            });
        }

        function refreshPlayerList() {
            location.reload();
        }
    </script>
    @endif
</x-app-layout>
