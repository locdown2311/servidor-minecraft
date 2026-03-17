<nav class="mc-nav" x-data="{ open: false }">
    <div class="mc-container">
        <div class="mc-nav-inner">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="mc-nav-logo">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="8" fill="url(#logo-gradient)"/>
                    <path d="M8 12h4v4H8v-4zm4 4h4v4h-4v-4zm4-4h4v4h-4v-4zm4 4h4v4h-4v-4zm-8-8h4v4h-4V8zm4 16h4v4h-4v-4z" fill="rgba(255,255,255,0.9)"/>
                    <defs>
                        <linearGradient id="logo-gradient" x1="0" y1="0" x2="32" y2="32">
                            <stop stop-color="#6c5ce7"/>
                            <stop offset="1" stop-color="#a29bfe"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span>MCHost</span>
            </a>

            <!-- Desktop Nav -->
            <div class="mc-nav-links">
                @auth
                    <a href="{{ route('dashboard') }}" class="mc-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('servers.create') }}" class="mc-nav-link {{ request()->routeIs('servers.create') ? 'active' : '' }}">Criar Servidor</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.index') }}" class="mc-nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">Admin</a>
                    @endif
                @else
                    <a href="#planos" class="mc-nav-link">Planos</a>
                    <a href="#features" class="mc-nav-link">Recursos</a>
                @endauth
            </div>

            <!-- User Menu / Auth Buttons -->
            <div class="mc-nav-actions">
                @auth
                    <div class="mc-dropdown" x-data="{ dropdownOpen: false }">
                        <button @click="dropdownOpen = !dropdownOpen" class="mc-nav-user-btn">
                            <span>{{ Auth::user()->name }}</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6l4 4 4-4"/></svg>
                        </button>
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition class="mc-dropdown-menu">
                            <a href="{{ route('profile.edit') }}" class="mc-dropdown-item">Perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="mc-dropdown-item mc-dropdown-item-full">Sair</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="mc-btn mc-btn-ghost">Entrar</a>
                    <a href="{{ route('register') }}" class="mc-btn mc-btn-primary">Criar Conta</a>
                @endauth
            </div>

            <!-- Mobile Toggle -->
            <button @click="open = !open" class="mc-nav-mobile-toggle">
                <svg x-show="!open" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M6 18L18 6"/></svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="open" x-transition class="mc-nav-mobile">
            @auth
                <a href="{{ route('dashboard') }}" class="mc-nav-mobile-link">Dashboard</a>
                <a href="{{ route('servers.create') }}" class="mc-nav-mobile-link">Criar Servidor</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.index') }}" class="mc-nav-mobile-link">Admin</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="mc-nav-mobile-link">Perfil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mc-nav-mobile-link mc-nav-mobile-link-full">Sair</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="mc-nav-mobile-link">Entrar</a>
                <a href="{{ route('register') }}" class="mc-nav-mobile-link">Criar Conta</a>
            @endauth
        </div>
    </div>
</nav>
