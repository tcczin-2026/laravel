<!-- index -->

@extends('layouts.app')

@section('titulo', 'Acessorios')

@section('conteudo')

@php
        $mostrarFiltro = request()->boolean('mostrarFiltro');

        $filtrosAtivos = collect(request()->only(['nome','plataformaacessorio','estado','cores','edicoes','retros']))
            ->filter(fn($v) => filled($v))
            ->count();

        $urlAbrirFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => 1]);
        $urlFecharFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => null]);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Acessorios</h1>
        <div class="d-flex gap-2">
            <a href="{{ $urlAbrirFiltro }}" class="btn btn-outline-light position-relative">
                <i class="bi bi-funnel"></i> Filtro
                @if ($filtrosAtivos > 0)
                    <span class="badge rounded-pill bg-primary position-absolute top-0 start-100 translate-middle">
                        {{ $filtrosAtivos }}
                    </span>
                @endif
            </a>
        <a href="{{ route('acessorio.create') }}" class="btn btn-primary">Novo acessorio</a>
        </div>
    </div>

    @if ($mostrarFiltro)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-funnel"></i> Filtrar acessorios</span>
                <a href="{{ $urlFecharFiltro }}" class="btn-close btn-close-white" aria-label="Fechar"></a>
            </div>

            <form method="GET" action="{{ route('acessorio.index') }}">
                <input type="hidden" name="mostrarFiltro" value="1">

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" placeholder="Buscar por nome"
                   value="{{ request('nome') }}">
        </div>

        <div class="col-md-2">
            <select name="plataforma" class="form-select">
                <option value="">Todas as plataformas</option>
                @foreach ($plataformas as $plataforma)
                    <option value="{{ $plataforma->id }}" @selected(request('plataforma') == $plataforma->id)>
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

        <a href="{{ route('acessorio.index', ['mostrarFiltro' => 1]) }}" class="btn btn-outline-light">Limpar filtros</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
    @endif
    {{-- FIM DO FILTRO --}}


    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Plataforma</th>
                    <th>Qtd.</th>
                    <th>Estoque</th>
                    <th>Estado</th>
                    <th>Vintage</th>
                    <th>Cor</th>
                    <th>Edicao</th>
                    <th style="width: 220px">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($acessorios as $acessorio)
                    <tr>
                        <td>{{ $acessorio->id }}</td>
                        <td>{{ $acessorio->nome }}</td>
                        <td>{{ $acessorio->plataforma->nome ?? '-' }}</td>
                        <td>{{ $acessorio->quantidade }}</td>
                        <td>
                            @if ($acessorio->quantidade > 0)
                                <span class="stock-pill stock-pill--ok">Em estoque</span>
                            @else
                                <span class="stock-pill stock-pill--out">Esgotado</span>
                            @endif
                        </td>
                        <td>{{ $acessorio->usado->nome ?? '-' }}</td>
                        <td>{{ $acessorio->retro->nome ?? '-' }}</td>
                        <td>{{ $acessorio->cor->nome ?? '-' }}</td>
                        <td>{{ $acessorio->edicaoEspecial->nome ?? '-' }}</td>
                        <td>

 <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#verModal{{ $acessorio->id }}">
                                Ver
                            </button>
                            <a href="{{ route('acessorio.edit', $acessorio->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('acessorio.destroy', $acessorio->id) }}" method="post"
                                  class="d-inline" onsubmit="return confirm('Excluir este acessorio?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">Nenhum acessorio cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
            
        </table>
    </div>
    {{-- A TABELA TERMINA AQUI. Os modais abaixo NÃO são mais filhos dela. --}}

    {{-- MODAIS DE DETALHES + HISTÓRICO (um por acessorio) --}}
    @foreach ($acessorios as $acessorio)
        <div class="modal fade" id="verModal{{ $acessorio->id }}" tabindex="-1" aria-labelledby="verModalLabel{{ $acessorio->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="verModalLabel{{ $acessorio->id }}">{{ $acessorio->nome }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <dl class="row mb-4">
                            <dt class="col-sm-4">Plataforma</dt>
                            <dd class="col-sm-8">{{ $acessorio->plataforma->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Quantidade</dt>
                            <dd class="col-sm-8">{{ $acessorio->quantidade }}</dd>

                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">{{ $acessorio->usado->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Cor</dt>
                            <dd class="col-sm-8">{{ $acessorio->cor->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Vintage</dt>
                            <dd class="col-sm-8">{{ $acessorio->retro->nome ?? '-' }}</dd>

                            <dt class="col-sm-4">Edição especial</dt>
                            <dd class="col-sm-8">{{ $acessorio->edicaoEspecial->nome ?? '-' }}</dd>
                        </dl>

                        <h6 class="mb-3">Histórico de alterações</h6>

                        @if ($acessorio->historico->isEmpty())
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
                                        @foreach ($acessorio->historico->sortByDesc('created_at') as $item)
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
                        <a href="{{ route('acessorio.edit', $acessorio->id) }}" class="btn btn-warning">Editar</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
