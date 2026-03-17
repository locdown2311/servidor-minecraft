<x-app-layout>
    @section('title', 'Criar Servidor')

    <x-slot name="header">
        <h1 class="mc-page-title">Criar Novo Servidor</h1>
    </x-slot>

    <div class="mc-container mc-section">
        <div class="mc-form-container">
            <form method="POST" action="{{ route('servers.store') }}" class="mc-form">
                @csrf

                <!-- Server Name -->
                <div class="mc-form-group">
                    <label for="name" class="mc-label">Nome do Servidor</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="mc-input" placeholder="Meu Servidor Minecraft" required>
                    @error('name')
                        <span class="mc-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Plan Selection -->
                <div class="mc-form-group">
                    <label class="mc-label">Selecione um Plano</label>
                    <div class="mc-plan-selector">
                        @foreach($plans as $plan)
                            <label class="mc-plan-option {{ $loop->index === 1 ? 'mc-plan-option-recommended' : '' }}">
                                <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                       {{ old('plan_id', $plans[1]->id ?? $plans[0]->id) == $plan->id ? 'checked' : '' }}>
                                <div class="mc-plan-option-content">
                                    <div class="mc-plan-option-header">
                                        <span class="mc-plan-option-name">{{ $plan->name }}</span>
                                        <span class="mc-plan-option-price">R$ {{ number_format($plan->price_monthly, 2, ',', '.') }}/mês</span>
                                    </div>
                                    <div class="mc-plan-option-specs">
                                        <span>{{ $plan->ram_label }} RAM</span>
                                        <span>{{ $plan->max_players }} jogadores</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('plan_id')
                        <span class="mc-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Server Type -->
                <div class="mc-form-group">
                    <label for="server_type" class="mc-label">Tipo de Servidor</label>
                    <select id="server_type" name="server_type" class="mc-select">
                        <option value="VANILLA" {{ old('server_type') == 'VANILLA' ? 'selected' : '' }}>Vanilla</option>
                        <option value="PAPER" {{ old('server_type') == 'PAPER' ? 'selected' : '' }}>Paper (Recomendado)</option>
                        <option value="SPIGOT" {{ old('server_type') == 'SPIGOT' ? 'selected' : '' }}>Spigot</option>
                        <option value="FORGE" {{ old('server_type') == 'FORGE' ? 'selected' : '' }}>Forge</option>
                        <option value="FABRIC" {{ old('server_type') == 'FABRIC' ? 'selected' : '' }}>Fabric</option>
                        <option value="BUKKIT" {{ old('server_type') == 'BUKKIT' ? 'selected' : '' }}>Bukkit</option>
                    </select>
                    @error('server_type')
                        <span class="mc-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Minecraft Version -->
                <div class="mc-form-group">
                    <label for="minecraft_version" class="mc-label">Versão do Minecraft</label>
                    <select id="minecraft_version" name="minecraft_version" class="mc-select">
                        <option value="latest" {{ old('minecraft_version') == 'latest' ? 'selected' : '' }}>Última versão</option>
                        <option value="1.21.4" {{ old('minecraft_version') == '1.21.4' ? 'selected' : '' }}>1.21.4</option>
                        <option value="1.21.3" {{ old('minecraft_version') == '1.21.3' ? 'selected' : '' }}>1.21.3</option>
                        <option value="1.20.6" {{ old('minecraft_version') == '1.20.6' ? 'selected' : '' }}>1.20.6</option>
                        <option value="1.20.4" {{ old('minecraft_version') == '1.20.4' ? 'selected' : '' }}>1.20.4</option>
                        <option value="1.19.4" {{ old('minecraft_version') == '1.19.4' ? 'selected' : '' }}>1.19.4</option>
                        <option value="1.18.2" {{ old('minecraft_version') == '1.18.2' ? 'selected' : '' }}>1.18.2</option>
                        <option value="1.16.5" {{ old('minecraft_version') == '1.16.5' ? 'selected' : '' }}>1.16.5</option>
                        <option value="1.12.2" {{ old('minecraft_version') == '1.12.2' ? 'selected' : '' }}>1.12.2</option>
                    </select>
                    @error('minecraft_version')
                        <span class="mc-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- MOTD -->
                <div class="mc-form-group">
                    <label for="motd" class="mc-label">Mensagem do Servidor (MOTD)</label>
                    <input type="text" id="motd" name="motd" value="{{ old('motd') }}"
                           class="mc-input" placeholder="Bem-vindo ao meu servidor!">
                    @error('motd')
                        <span class="mc-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="mc-btn mc-btn-primary mc-btn-lg mc-btn-block">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                    Criar Servidor
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
