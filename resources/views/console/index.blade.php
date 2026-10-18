@extends('layouts.app')

@section('titulo', 'Consoles')

@section('conteudo')

@php
        $mostrarFiltro = request()->boolean('mostrarFiltro');

        $filtrosAtivos = collect(request()->only(['nome','plataformaConsole','estado','cores','digitais','desbloqueados','edicoes','retros']))
            ->filter(fn($v) => filled($v))
            ->count();

        $urlAbrirFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => 1]);
        $urlFecharFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => null]);
    @endphp
<link rel="stylesheet" href="App\Http\resources\css\app.css">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 mb-0">Consoles</h1>
        <div class="d-flex gap-2">
            <a href="{{ $urlAbrirFiltro }}" class="btn btn-outline-light position-relative">
                <i class="bi bi-funnel"></i> Filtro
                @if ($filtrosAtivos > 0)
                    <span class="badge rounded-pill bg-primary position-absolute top-0 start-100 translate-middle">
                        {{ $filtrosAtivos }}
                    </span>
                @endif
            </a>
        <a href="{{ route('console.create') }}" class="btn btn-primary">Novo console</a>
        </div>
    </div>

    @if ($mostrarFiltro)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-funnel"></i> Filtrar consoles</span>
                <a href="{{ $urlFecharFiltro }}" class="btn-close btn-close-white" aria-label="Fechar"></a>
            </div>

            <form method="GET" action="{{ route('console.index') }}">
                <input type="hidden" name="mostrarFiltro" value="1">

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" placeholder="Buscar por nome"
                   value="{{ request('nome') }}">
        </div>

        <div class="col-md-2">
            <select name="plataformaConsole" class="form-select">
                <option value="">Todas as plataformas</option>
                @foreach ($plataformas as $plataforma)
                    <option value="{{ $plataforma->id }}" @selected(request('plataformaConsole') == $plataforma->id)>
                        {{ $plataforma->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="">Todos os estados</option>
                @foreach ($usados as $usado)
                    <option value="{{ $usado->id }}" @selected(request('estado') == $usado->id)>
                        {{ $usado->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="cores" class="form-select">
                <option value="">Todas as cores</option>
                @foreach ($cores as $cor)
                    <option value="{{ $cor->id }}" @selected(request('cores') == $cor->id)>
                        {{ $cor->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="digitais" class="form-select">
                <option value="">Todas as digitais</option>
                @foreach ($digitais as $digital)
                    <option value="{{ $digital->id }}" @selected(request('digitais') == $digital->id)>
                        {{ $digital->nome }}
                    </option>
                @endforeach
            </select>
           </div> 

                    <div class="col-md-2">
            <select name="desbloqueados" class="form-select">
                <option value="">Todos desbloqueados</option>
                @foreach ($desbloqueados as $desbloqueado)
                    <option value="{{ $desbloqueado->id }}" @selected(request('desbloqueados') == $desbloqueado->id)>
                        {{ $desbloqueado->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="edicoes" class="form-select">
                <option value="">Todos edicoes</option>
                @foreach ($edicoes as $edicao)
                    <option value="{{ $edicao->id }}" @selected(request('edicoes') == $edicao->id)>
                        {{ $edicao->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="retros" class="form-select">
                <option value="">Todas as retros</option>
                @foreach ($retros as $retro)
                    <option value="{{ $retro->id }}" @selected(request('retros') == $retro->id)>
                        {{ $retro->nome }}
                    </option>
                @endforeach
            </select>
        
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('console.index', ['mostrarFiltro' => 1]) }}" class="btn btn-outline-light">Limpar filtros</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
    @endif
    {{-- FIM DO FILTRO --}}
 
    @include('partials.lista.tabela', [
        'itens'   => $consoles,
        'tipo'    => 'console',
        'artigo'  => 'este console',
        'vazio'   => 'Nenhum console cadastrado.',
        'caracteristicas' => fn ($c) => [
            'estado' => $c->usado->nome ?? null, 'cor' => $c->cor->nome ?? null, 'leitor' => $c->digital->nome ?? null,
            'armazenamento' => $c->armazenamento ? $c->armazenamento_formatado : null, 'retrô' => $c->retro->nome ?? null,
            'desbloqueio' => $c->desbloqueado->nome ?? null, 'edição' => $c->edicaoEspecial->nome ?? null,
        ],
    ])

    <div class="paginacao mt-3">
        {{ $consoles->links() }}
    </div>
    {{-- A TABELA TERMINA AQUI. Os modais abaixo NÃO são mais filhos dela. --}}

    {{-- MODAIS DE DETALHES + HISTÓRICO (um por console) --}}
    @foreach ($consoles as $console)
        <div class="modal fade" id="verModal{{ $console->id }}" tabindex="-1" aria-labelledby="verModalLabel{{ $console->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="verModalLabel{{ $console->id }}">{{ $console->nome }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <dl class="row mb-4">
                            <dt class="col-sm-4">Plataforma</dt>
                            <dd class="col-sm-8">{{ $console->plataforma->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Quantidade</dt>
                            <dd class="col-sm-8">{{ $console->quantidade }}</dd>

                            <dt class="col-sm-4">Valor</dt>
                            <dd class="col-sm-8">{{ $console->valor_formatado }}</dd>

                            <dt class="col-sm-4">Armazenamento</dt>
                            <dd class="col-sm-8">{{ $console->armazenamento_formatado }}</dd>

                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">{{ $console->usado->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Cor</dt>
                            <dd class="col-sm-8">{{ $console->cor->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Vintage</dt>
                            <dd class="col-sm-8">{{ $console->retro->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Edição especial</dt>
                            <dd class="col-sm-8">{{ $console->edicaoEspecial->nome ?? '-' }}</dd>
                        </dl>

                        <h6 class="mb-3">Histórico de alterações</h6>

                        @include('partials.historico-tabela', ['historico' => $console->historico, 'tipo' => 'console'])
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('console.edit', $console->id) }}" class="btn btn-warning">Editar</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection