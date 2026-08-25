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

<div class="app-shell">
    <aside class="app-sidebar">
        <div class="app-brand">
            <div class="logo-dot"></div>
            <div class="wordmark">Colecao Games<span>Acervo retro</span></div>
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

            <div class="app-nav-label">Tabelas auxiliares</div>
            @foreach (\App\Http\Controllers\AuxiliarController::tipos() as $tipoNav => $configNav)
                <a href="{{ route('auxiliar.index', $tipoNav) }}"
                   class="{{ request()->routeIs('auxiliar.*') && request()->route('tipo') === $tipoNav ? 'active' : '' }}">
                    <span class="icon">&#8226;</span> {{ $configNav['titulo'] }}
                </a>
            @endforeach
        </nav>
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

</body>
</html>
