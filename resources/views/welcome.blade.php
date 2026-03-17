@section('title', 'Hospedagem Minecraft Premium')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hospedagem premium de servidores Minecraft. Crie seu servidor em segundos com acesso FTP, suporte a mods e painel completo.">
    <title>MCHost — Hospedagem Minecraft Premium</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

<!-- Navbar -->
<nav class="mc-landing-nav">
    <div class="mc-container mc-nav-inner">
        <a href="{{ route('home') }}" class="mc-nav-logo">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <rect width="32" height="32" rx="8" fill="url(#lg)"/>
                <path d="M8 12h4v4H8v-4zm4 4h4v4h-4v-4zm4-4h4v4h-4v-4zm4 4h4v4h-4v-4zm-8-8h4v4h-4V8zm4 16h4v4h-4v-4z" fill="rgba(255,255,255,0.9)"/>
                <defs><linearGradient id="lg" x1="0" y1="0" x2="32" y2="32"><stop stop-color="#6c5ce7"/><stop offset="1" stop-color="#a29bfe"/></linearGradient></defs>
            </svg>
            <span>MCHost</span>
        </a>
        <div class="mc-nav-links">
            <a href="#planos" class="mc-nav-link">Planos</a>
            <a href="#features" class="mc-nav-link">Recursos</a>
        </div>
        <div class="mc-nav-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="mc-btn mc-btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="mc-btn mc-btn-ghost">Entrar</a>
                <a href="{{ route('register') }}" class="mc-btn mc-btn-primary">Criar Conta</a>
            @endauth
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="mc-hero">
    <div class="mc-hero-bg">
        <div class="mc-hero-particles"></div>
        <div class="mc-hero-grid"></div>
    </div>
    <div class="mc-container mc-hero-content">
        <div class="mc-hero-badge">
            <span class="mc-hero-badge-dot"></span>
            Servidores disponíveis agora
        </div>
        <h1 class="mc-hero-title">
            Seu Servidor<br>
            <span class="mc-gradient-text">Minecraft</span> em Segundos
        </h1>
        <p class="mc-hero-subtitle">
            Hospedagem de alta performance com provisionamento automático, acesso FTP completo e suporte a mods.
            Comece a jogar com seus amigos agora.
        </p>
        <div class="mc-hero-actions">
            <a href="{{ route('register') }}" class="mc-btn mc-btn-primary mc-btn-lg">
                Começar Agora
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"/></svg>
            </a>
            <a href="#planos" class="mc-btn mc-btn-outline mc-btn-lg">Ver Planos</a>
        </div>
        <div class="mc-hero-stats">
            <div class="mc-hero-stat">
                <span class="mc-hero-stat-value">99.9%</span>
                <span class="mc-hero-stat-label">Uptime</span>
            </div>
            <div class="mc-hero-stat-divider"></div>
            <div class="mc-hero-stat">
                <span class="mc-hero-stat-value">&lt;50ms</span>
                <span class="mc-hero-stat-label">Latência</span>
            </div>
            <div class="mc-hero-stat-divider"></div>
            <div class="mc-hero-stat">
                <span class="mc-hero-stat-value">SSD</span>
                <span class="mc-hero-stat-label">NVMe</span>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="mc-features">
    <div class="mc-container">
        <div class="mc-section-header">
            <h2 class="mc-section-title">Por que escolher a <span class="mc-gradient-text">MCHost</span>?</h2>
            <p class="mc-section-subtitle">Tudo o que você precisa para o servidor Minecraft dos sonhos</p>
        </div>
        <div class="mc-features-grid">
            <div class="mc-feature-card">
                <div class="mc-feature-icon" style="--accent: #6c5ce7;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <h3>Deploy Instantâneo</h3>
                <p>Seu servidor é criado automaticamente via Docker em segundos. Sem espera, sem complicação.</p>
            </div>
            <div class="mc-feature-card">
                <div class="mc-feature-icon" style="--accent: #00b894;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                </div>
                <h3>Acesso FTP</h3>
                <p>Cada servidor vem com FTP dedicado. Envie mods, mapas e configurações facilmente.</p>
            </div>
            <div class="mc-feature-card">
                <div class="mc-feature-icon" style="--accent: #e17055;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                </div>
                <h3>Painel Completo</h3>
                <p>Inicie, pare, reinicie e veja logs do seu servidor diretamente pelo navegador.</p>
            </div>
            <div class="mc-feature-card">
                <div class="mc-feature-icon" style="--accent: #fdcb6e;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>Proteção DDoS</h3>
                <p>Infraestrutura protegida contra ataques para seu servidor ficar sempre online.</p>
            </div>
            <div class="mc-feature-card">
                <div class="mc-feature-icon" style="--accent: #a29bfe;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <h3>Suporte a Mods</h3>
                <p>Vanilla, Paper, Forge, Fabric, Spigot, Bukkit. Escolha o tipo que preferir.</p>
            </div>
            <div class="mc-feature-card">
                <div class="mc-feature-icon" style="--accent: #00cec9;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                </div>
                <h3>Hardware Premium</h3>
                <p>CPUs de última geração e SSDs NVMe para o menor TPS possível no seu servidor.</p>
            </div>
        </div>
    </div>
</section>

<!-- Plans Section -->
<section id="planos" class="mc-plans">
    <div class="mc-container">
        <div class="mc-section-header">
            <h2 class="mc-section-title">Escolha seu <span class="mc-gradient-text">Plano</span></h2>
            <p class="mc-section-subtitle">Hospedagem que cabe no seu bolso. Cancele quando quiser.</p>
        </div>
        <div class="mc-plans-grid">
            @foreach($plans as $plan)
                <div class="mc-plan-card {{ $plan->slug === 'pro' ? 'mc-plan-featured' : '' }}">
                    @if($plan->slug === 'pro')
                        <div class="mc-plan-badge">Mais Popular</div>
                    @endif
                    <div class="mc-plan-header">
                        <h3 class="mc-plan-name">{{ $plan->name }}</h3>
                        <p class="mc-plan-description">{{ $plan->description }}</p>
                    </div>
                    <div class="mc-plan-price">
                        <span class="mc-plan-currency">R$</span>
                        <span class="mc-plan-value">{{ number_format($plan->price_monthly, 2, ',', '.') }}</span>
                        <span class="mc-plan-period">/mês</span>
                    </div>
                    <ul class="mc-plan-features">
                        @foreach($plan->features ?? [] as $feature)
                            <li>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.333 4L6 11.333 2.667 8" stroke="#6c5ce7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="mc-btn {{ $plan->slug === 'pro' ? 'mc-btn-primary' : 'mc-btn-outline' }} mc-btn-block">
                        Começar com {{ $plan->name }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="mc-cta">
    <div class="mc-container">
        <div class="mc-cta-inner">
            <h2>Pronto para criar seu servidor?</h2>
            <p>Comece em menos de 60 segundos. Sem complicação.</p>
            <a href="{{ route('register') }}" class="mc-btn mc-btn-primary mc-btn-lg">
                Criar Conta Grátis
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"/></svg>
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="mc-footer">
    <div class="mc-container">
        <p>&copy; {{ date('Y') }} MCHost. Todos os direitos reservados.</p>
    </div>
</footer>

</body>
</html>
