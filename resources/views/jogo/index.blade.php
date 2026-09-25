@extends('layouts.app')

@section('titulo', 'Jogos')

@section('conteudo')

@php
        $mostrarFiltro = request()->boolean('mostrarFiltro');

        $filtrosAtivos = collect(request()->only(['nome','plataformajogo','estado','cores','edicoes','retros']))
            ->filter(fn($v) => filled($v))
            ->count();

        $urlAbrirFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => 1]);
        $urlFecharFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => null]);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Jogos</h1>
        <div class="d-flex gap-2">
            <a href="{{ $urlAbrirFiltro }}" class="btn btn-outline-light position-relative">
                <i class="bi bi-funnel"></i> Filtro
                @if ($filtrosAtivos > 0)
                    <span class="badge rounded-pill bg-primary position-absolute top-0 start-100 translate-middle">
                        {{ $filtrosAtivos }}
                    </span>
                @endif
            </a>
        <a href="{{ route('jogo.create') }}" class="btn btn-primary">Novo jogo</a>
        </div>
    </div>

    @if ($mostrarFiltro)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-funnel"></i> Filtrar jogos</span>
                <a href="{{ $urlFecharFiltro }}" class="btn-close btn-close-white" aria-label="Fechar"></a>
            </div>

            <form method="GET" action="{{ route('jogo.index') }}">
                <input type="hidden" name="mostrarFiltro" value="1">

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" placeholder="Buscar por nome"
                   value="{{ request('nome') }}">
        </div>

        <div class="col-md-2">
            <select name="plataformajogo" class="form-select">
                <option value="">Todas as plataformas</option>
                @foreach ($plataformas as $plataforma)
                    <option value="{{ $plataforma->id }}" @selected(request('plataformajogo') == $plataforma->id)>
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
                    <a href="{{ route('jogo.index', ['mostrarFiltro' => 1]) }}" class="btn btn-outline-light">Limpar filtros</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
    @endif
    {{-- FIM DO FILTRO --}}

    <div class="table-responsive">
        <table class="table table-dark align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Plataforma</th>
                    <th>Qtd.</th>
                    <th>Estoque</th>
                    <th>Estado</th>
                    <th>Vintage</th>
                    <th>Edicao</th>
                    <th style="width: 220px">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jogos as $jogo)
                    <tr>
                        <td>{{ $jogo->id }}</td>
                        <td>{{ $jogo->nome }}</td>
                        <td>{{ $jogo->plataforma->nome ?? '-' }}</td>
                        <td>{{ $jogo->quantidade }}</td>
                        <td>
                            @if ($jogo->quantidade > 0)
                                <span class="stock-pill stock-pill--ok">Em estoque</span>
                            @else
                                <span class="stock-pill stock-pill--out">Esgotado</span>
                            @endif
                        </td>
                        <td>{{ $jogo->usado->nome ?? '-' }}</td>
                        <td>{{ $jogo->retro->nome ?? '-' }}</td>
                        <td>{{ $jogo->edicaoEspecial->nome ?? '-' }}</td>
                        <td>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#verModal{{ $jogo->id }}">
                                Ver
                            </button>
                            <a href="{{ route('jogo.edit', $jogo->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('jogo.destroy', $jogo->id) }}" method="post"
                                  class="d-inline" onsubmit="return confirm('Excluir este jogo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Nenhum jogo cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- A TABELA TERMINA AQUI. Os modais abaixo NÃO são mais filhos dela. --}}

    {{-- MODAIS DE DETALHES + HISTÓRICO (um por jogo) --}}
    @foreach ($jogos as $jogo)
        <div class="modal fade" id="verModal{{ $jogo->id }}" tabindex="-1" aria-labelledby="verModalLabel{{ $jogo->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="verModalLabel{{ $jogo->id }}">{{ $jogo->nome }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <dl class="row mb-4">
                            <dt class="col-sm-4">Plataforma</dt>
                            <dd class="col-sm-8">{{ $jogo->plataforma->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Quantidade</dt>
                            <dd class="col-sm-8">{{ $jogo->quantidade }}</dd>

                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">{{ $jogo->usado->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Vintage</dt>
                            <dd class="col-sm-8">{{ $jogo->retro->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Edição especial</dt>
                            <dd class="col-sm-8">{{ $jogo->edicaoEspecial->nome ?? '-' }}</dd>
                        </dl>

                        <h6 class="mb-3">Histórico de alterações</h6>

                        @if ($jogo->historico->isEmpty())
                            <p class="text-muted mb-0">Nenhuma alteração registrada.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-dark table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Ação</th>
                                            <th>Campo</th>
                                            <th>De</th>
                                            <th>Para</th>
                                            <th>Usuário</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($jogo->historico->sortByDesc('created_at') as $item)
                                            <tr>
                                                <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    @if ($item->acao === 'criado')
                                                        <span class="badge bg-success">Criado</span>
                                                    @elseif ($item->acao === 'excluido')
                                                        <span class="badge bg-danger">Excluído</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Atualizado</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->campo ?? '-' }}</td>
                                                <td>{{ $item->valor_anterior ?? '-' }}</td>
                                                <td>{{ $item->valor_novo ?? '-' }}</td>
                                                <td>{{ $item->usuario ?? 'Sistema' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('jogo.edit', $jogo->id) }}" class="btn btn-warning">Editar</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
