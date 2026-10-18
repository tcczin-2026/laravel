@extends('layouts.app')

@section('titulo', 'Controles')

@section('conteudo')
    @php
        $mostrarFiltro = request()->boolean('mostrarFiltro');

        $filtrosAtivos = collect(request()->only(['nome','plataformacontrole','estado','cores','edicoes','retros']))
            ->filter(fn($v) => filled($v))
            ->count();

        $urlAbrirFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => 1]);
        $urlFecharFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => null]);
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 mb-0">Controles</h1>
        <div class="d-flex gap-2">
            <a href="{{ $urlAbrirFiltro }}" class="btn btn-outline-light position-relative">
                <i class="bi bi-funnel"></i> Filtro
                @if ($filtrosAtivos > 0)
                    <span class="badge rounded-pill bg-primary position-absolute top-0 start-100 translate-middle">
                        {{ $filtrosAtivos }}
                    </span>
                @endif
            </a>
            <a href="{{ route('controle.create') }}" class="btn btn-primary">Novo controle</a>
        </div>
    </div>

    @if ($mostrarFiltro)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-funnel"></i> Filtrar controles</span>
                <a href="{{ $urlFecharFiltro }}" class="btn-close btn-close-white" aria-label="Fechar"></a>
            </div>

            <form method="GET" action="{{ route('controle.index') }}">
                <input type="hidden" name="mostrarFiltro" value="1">

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" placeholder="Buscar por nome"
                                   value="{{ request('nome') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Plataforma</label>
                            <select name="plataformacontrole" class="form-select">
                                <option value="">Todas as plataformas</option>
                                @foreach ($plataformas as $plataforma)
                                    <option value="{{ $plataforma->id }}" @selected(request('plataformacontrole') == $plataforma->id)>
                                        {{ $plataforma->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos os estados</option>
                                @foreach ($usados as $usado)
                                    <option value="{{ $usado->id }}" @selected(request('estado') == $usado->id)>
                                        {{ $usado->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cor</label>
                            <select name="cores" class="form-select">
                                <option value="">Todas as cores</option>
                                @foreach ($cores as $cor)
                                    <option value="{{ $cor->id }}" @selected(request('cores') == $cor->id)>
                                        {{ $cor->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Edição</label>
                            <select name="edicoes" class="form-select">
                                <option value="">Todas as edições</option>
                                @foreach ($edicoes as $edicao)
                                    <option value="{{ $edicao->id }}" @selected(request('edicoes') == $edicao->id)>
                                        {{ $edicao->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Retrocompatibilidade</label>
                            <select name="retros" class="form-select">
                                <option value="">Todas as retros</option>
                                @foreach ($retros as $retro)
                                    <option value="{{ $retro->id }}" @selected(request('retros') == $retro->id)>
                                        {{ $retro->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('controle.index', ['mostrarFiltro' => 1]) }}" class="btn btn-outline-light">Limpar filtros</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
    @endif

    @include('partials.lista.tabela', [
        'itens'   => $controles,
        'tipo'    => 'controle',
        'artigo'  => 'este controle',
        'vazio'   => 'Nenhum controle cadastrado.',
        'caracteristicas' => fn ($c) => [
            'estado' => $c->usado->nome ?? null, 'cor' => $c->cor->nome ?? null,
            'retrô' => $c->retro->nome ?? null, 'edição' => $c->edicaoEspecial->nome ?? null,
        ],
    ])

    <div class="paginacao mt-3">
        {{ $controles->links() }}
    </div>
    {{-- A TABELA TERMINA AQUI. Os modais abaixo NÃO são mais filhos dela. --}}

    {{-- MODAIS DE DETALHES + HISTÓRICO (um por controle) --}}
    @foreach ($controles as $controle)
        <div class="modal fade" id="verModal{{ $controle->id }}" tabindex="-1" aria-labelledby="verModalLabel{{ $controle->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="verModalLabel{{ $controle->id }}">{{ $controle->nome }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <dl class="row mb-4">
                            <dt class="col-sm-4">Plataforma</dt>
                            <dd class="col-sm-8">{{ $controle->plataforma->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Quantidade</dt>
                            <dd class="col-sm-8">{{ $controle->quantidade }}</dd>

                            <dt class="col-sm-4">Valor</dt>
                            <dd class="col-sm-8">{{ $controle->valor_formatado }}</dd>

                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">{{ $controle->usado->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Cor</dt>
                            <dd class="col-sm-8">{{ $controle->cor->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Vintage</dt>
                            <dd class="col-sm-8">{{ $controle->retro->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Edição especial</dt>
                            <dd class="col-sm-8">{{ $controle->edicaoEspecial->nome ?? '-' }}</dd>
                        </dl>

                        <h6 class="mb-3">Histórico de alterações</h6>

                        @include('partials.historico-tabela', ['historico' => $controle->historico, 'tipo' => 'controle'])
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('controle.edit', $controle->id) }}" class="btn btn-warning">Editar</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection