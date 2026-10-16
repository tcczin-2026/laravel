<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Colecao de Games')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">
</head>
<body>

{{-- Barra do topo: só aparece em telas pequenas (abaixo de lg) --}}
<header class="app-mobilebar d-lg-none">
    <div class="app-brand">
        <div class="logo-dot"></div>
        <div class="wordmark">Colecao Games<span>Acervo retro</span></div>
    </div>
    <button class="btn btn-secondary app-menu-btn" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Abrir menu">
        &#9776;
    </button>
</header>

<div class="app-shell">
    {{-- Em telas grandes é a sidebar fixa; em telas pequenas vira um menu lateral (offcanvas) --}}
    <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar">
        <div class="app-brand">
            <div class="logo-dot"></div>
            <div class="wordmark">Colecao Games<span>Acervo retro</span></div>
            <button type="button" class="btn-close btn-close-white ms-auto d-lg-none"
                    data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Fechar"></button>
        </div>

        <nav class="app-nav">
            <a href="{{ route('painel.index') }}" class="{{ request()->routeIs('painel.index') ? 'active' : '' }}">
                <span class="icon">&#9635;</span> Dashboard
            </a>
            <a href="{{ route('console.index') }}" class="{{ request()->routeIs('console.*') ? 'active' : '' }}">
                <span class="icon">&#127918;</span> Consoles
            </a>
            <a href="{{ route('controle.index') }}" class="{{ request()->routeIs('controle.*') ? 'active' : '' }}">
                <span class="icon">&#127920;</span> Controles
            </a>
            <a href="{{ route('jogo.index') }}" class="{{ request()->routeIs('jogo.*') ? 'active' : '' }}">
                <span class="icon">&#128191;</span> Jogos
            </a>
            <a href="{{ route('acessorio.index') }}" class="{{ request()->routeIs('acessorio.*') ? 'active' : '' }}">
                <span class="icon">&#127911;</span> Acessorios
            </a>

            {{-- Só o gerente vê (e acessa) usuários e tabelas auxiliares --}}
            @if (auth()->user()?->isGerente())
                <div class="app-nav-label">Administração</div>
                <a href="{{ route('usuario.index') }}" class="{{ request()->routeIs('usuario.*') ? 'active' : '' }}">
                    <span class="icon">&#128100;</span> Usuários
                </a>

                <div class="app-nav-label">Tabelas auxiliares</div>
                @foreach (\App\Http\Controllers\AuxiliarController::tipos() as $tipoNav => $configNav)
                    <a href="{{ route('auxiliar.index', $tipoNav) }}"
                       class="{{ request()->routeIs('auxiliar.*') && request()->route('tipo') === $tipoNav ? 'active' : '' }}">
                        <span class="icon">&#8226;</span> {{ $configNav['titulo'] }}
                    </a>
                @endforeach
            @endif
        </nav>

        @auth
            <div class="app-user">
                <div class="app-user-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ \App\Models\User::PERFIS[auth()->user()->perfil] ?? auth()->user()->perfil }}</span>
                </div>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-secondary w-100">Sair</button>
                </form>
            </div>
        @endauth
    </aside>

    <div class="app-main">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Corrija os erros abaixo:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('conteudo')
    </div>
</div>

{{-- Sem isso, data-bs-toggle="modal", dropdowns, etc não funcionam --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>