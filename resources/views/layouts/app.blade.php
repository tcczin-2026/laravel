<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Colecao de Games')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('painel.index') }}">Colecao de Games</a>
        <div class="navbar-nav">
            <a class="nav-link" href="{{ route('console.index') }}">Consoles</a>
            <a class="nav-link" href="{{ route('controle.index') }}">Controles</a>
            <a class="nav-link" href="{{ route('jogo.index') }}">Jogos</a>
            <a class="nav-link" href="{{ route('acessorio.index') }}">Acessorios</a>
        </div>
    </div>
</nav>

<div class="container pb-5">

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

</body>
</html>
