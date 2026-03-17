<x-app-layout>
    @section('title', $server->name . ' — Configurações')

    <div class="mc-panel">
        @include('servers._sidebar')

        <div class="mc-panel-content">
            <div class="mc-panel-header">
                <h2 class="mc-panel-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                    Configurações do Servidor
                </h2>
            </div>

            @if(!$server->isRunning())
                <div class="mc-panel-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4"/></svg>
                    <h3>Servidor Offline</h3>
                    <p>Inicie o servidor para editar as configurações.</p>
                </div>
            @elseif(empty($properties))
                <div class="mc-panel-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <h3>Não foi possível ler as configurações</h3>
                    <p>O arquivo server.properties pode não estar disponível ainda. Tente novamente após o servidor iniciar completamente.</p>
                </div>
            @else
                <form method="POST" action="{{ route('servers.settings.save', $server) }}">
                    @csrf

                    <div class="mc-settings-notice">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <span>Após salvar, <strong>reinicie o servidor</strong> para aplicar as alterações.</span>
                    </div>

                    @php
                        $sections = [
                            'Geral' => ['server-port', 'motd', 'max-players', 'gamemode', 'difficulty', 'level-name', 'level-seed', 'level-type', 'server-name', 'online-mode'],
                            'Gameplay' => ['pvp', 'allow-flight', 'allow-nether', 'spawn-animals', 'spawn-monsters', 'spawn-npcs', 'spawn-protection', 'generate-structures', 'hardcore', 'force-gamemode'],
                            'Mundo' => ['view-distance', 'simulation-distance', 'max-world-size', 'max-build-height', 'enable-command-block'],
                            'Rede' => ['network-compression-threshold', 'rate-limit', 'player-idle-timeout', 'white-list', 'enforce-whitelist', 'op-permission-level'],
                            'Avançado' => ['enable-rcon', 'rcon.port', 'rcon.password', 'enable-query', 'query.port', 'resource-pack', 'resource-pack-sha1'],
                        ];

                        $booleanProps = ['online-mode', 'pvp', 'allow-flight', 'allow-nether', 'spawn-animals', 'spawn-monsters', 'spawn-npcs', 'generate-structures', 'hardcore', 'force-gamemode', 'enable-command-block', 'white-list', 'enforce-whitelist', 'enable-rcon', 'enable-query'];

                        $selectProps = [
                            'gamemode' => ['survival' => 'Survival', 'creative' => 'Creative', 'adventure' => 'Adventure', 'spectator' => 'Spectator'],
                            'difficulty' => ['peaceful' => 'Peaceful', 'easy' => 'Easy', 'normal' => 'Normal', 'hard' => 'Hard'],
                            'level-type' => ['minecraft:normal' => 'Normal', 'minecraft:flat' => 'Flat', 'minecraft:large_biomes' => 'Large Biomes', 'minecraft:amplified' => 'Amplified'],
                        ];

                        $propLabels = [
                            'server-port' => 'Porta', 'motd' => 'MOTD', 'max-players' => 'Max Jogadores',
                            'gamemode' => 'Modo de Jogo', 'difficulty' => 'Dificuldade', 'level-name' => 'Nome do Mundo',
                            'level-seed' => 'Seed', 'level-type' => 'Tipo de Mundo', 'server-name' => 'Nome do Servidor',
                            'online-mode' => 'Modo Online', 'pvp' => 'PvP', 'allow-flight' => 'Permitir Voo',
                            'allow-nether' => 'Nether', 'spawn-animals' => 'Animais', 'spawn-monsters' => 'Monstros',
                            'spawn-npcs' => 'NPCs', 'spawn-protection' => 'Proteção de Spawn', 'generate-structures' => 'Estruturas',
                            'hardcore' => 'Hardcore', 'force-gamemode' => 'Forçar Gamemode',
                            'view-distance' => 'Distância de Visão', 'simulation-distance' => 'Distância de Simulação',
                            'max-world-size' => 'Tamanho Máx. do Mundo', 'max-build-height' => 'Altura Máx.',
                            'enable-command-block' => 'Command Blocks', 'network-compression-threshold' => 'Compressão de Rede',
                            'rate-limit' => 'Rate Limit', 'player-idle-timeout' => 'Timeout de Inatividade',
                            'white-list' => 'Whitelist', 'enforce-whitelist' => 'Forçar Whitelist',
                            'op-permission-level' => 'Nível OP', 'enable-rcon' => 'RCON',
                            'rcon.port' => 'Porta RCON', 'rcon.password' => 'Senha RCON',
                            'enable-query' => 'Query', 'query.port' => 'Porta Query',
                            'resource-pack' => 'Resource Pack URL', 'resource-pack-sha1' => 'Resource Pack SHA1',
                        ];
                    @endphp

                    @foreach($sections as $sectionName => $sectionKeys)
                        @php
                            $visibleKeys = array_filter($sectionKeys, fn($k) => isset($properties[$k]));
                        @endphp
                        @if(!empty($visibleKeys))
                            <div class="mc-settings-section">
                                <h3 class="mc-settings-section-title">{{ $sectionName }}</h3>
                                <div class="mc-settings-grid">
                                    @foreach($visibleKeys as $key)
                                        <div class="mc-settings-field">
                                            <label class="mc-settings-label" for="prop-{{ $key }}">{{ $propLabels[$key] ?? $key }}</label>

                                            @if(in_array($key, $booleanProps))
                                                <select name="properties[{{ $key }}]" id="prop-{{ $key }}" class="mc-select mc-select-sm">
                                                    <option value="true" {{ $properties[$key] === 'true' ? 'selected' : '' }}>Sim</option>
                                                    <option value="false" {{ $properties[$key] === 'false' ? 'selected' : '' }}>Não</option>
                                                </select>
                                            @elseif(isset($selectProps[$key]))
                                                <select name="properties[{{ $key }}]" id="prop-{{ $key }}" class="mc-select mc-select-sm">
                                                    @foreach($selectProps[$key] as $val => $label)
                                                        <option value="{{ $val }}" {{ $properties[$key] === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" name="properties[{{ $key }}]" id="prop-{{ $key }}"
                                                       value="{{ $properties[$key] }}" class="mc-input mc-input-sm">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Remaining properties not in sections --}}
                    @php
                        $allSectionKeys = collect($sections)->flatten()->toArray();
                        $otherKeys = array_diff(array_keys($properties), $allSectionKeys);
                    @endphp
                    @if(!empty($otherKeys))
                        <div class="mc-settings-section">
                            <h3 class="mc-settings-section-title">Outros</h3>
                            <div class="mc-settings-grid">
                                @foreach($otherKeys as $key)
                                    <div class="mc-settings-field">
                                        <label class="mc-settings-label" for="prop-{{ $key }}">{{ $key }}</label>
                                        <input type="text" name="properties[{{ $key }}]" id="prop-{{ $key }}"
                                               value="{{ $properties[$key] }}" class="mc-input mc-input-sm">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mc-settings-actions">
                        <button type="submit" class="mc-btn mc-btn-primary mc-btn-lg">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
