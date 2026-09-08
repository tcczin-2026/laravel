@extends('layouts.app')

@section('titulo', 'Controles')

@section('conteudo')
    @php
        // Parâmetro que controla, via PHP, se o painel de filtro aparece ou não.
        $mostrarFiltro = request()->boolean('mostrarFiltro');

        $filtrosAtivos = collect(request()->only(['nome','plataformacontrole','estado','cores','edicoes','retros']))
            ->filter(fn($v) => filled($v))
            ->count();

        // Monta a URL para abrir o painel, preservando os filtros já aplicados.
        $urlAbrirFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => 1]);

        // Monta a URL para fechar o painel, removendo só o parâmetro mostrarFiltro.
        $urlFecharFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => null]);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
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

    {{-- PAINEL DE FILTRO (controlado 100% via PHP, sem JS) --}}
    @if ($mostrarFiltro)
        <div class="card bg-dark border-secondary mb-4">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <span><i class="bi bi-funnel"></i> Filtrar controles</span>
                <a href="{{ $urlFecharFiltro }}" class="btn-close btn-close-white" aria-label="Fechar"></a>
            </div>

            <form method="GET" action="{{ route('controle.index') }}">
                {{-- Mantém o painel aberto após aplicar o filtro --}}
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

                <div class="card-footer border-secondary d-flex justify-content-end gap-2">
                    <a href="{{ route('controle.index', ['mostrarFiltro' => 1]) }}" class="btn btn-outline-light">Limpar filtros</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
    @endif
    {{-- FIM DO PAINEL DE FILTRO --}}

    <div class="table-responsive">
        <table class="table table-dark align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>plataforma</th>
                    <th>Qtd.</th>
                    <th>Estoque</th>
                    <th>Cor</th>
                    <th>Vintage</th>
                    <th>Estado</th>
                    <th>Edicao</th>
                    <th style="width: 220px">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($controles as $controle)
                    <tr>
                        <td>{{ $controle->id }}</td>
                        <td>{{ $controle->nome }}</td>
                        <td>{{ $controle->plataforma->nome ?? '-' }}</td>
                        <td>{{ $controle->quantidade }}</td>
                        <td>
                            @if ($controle->quantidade > 0)
                                <span class="stock-pill stock-pill--ok">Em estoque</span>
                            @else
                                <span class="stock-pill stock-pill--out">Esgotado</span>
                            @endif
                        </td>
                        <td>{{ $controle->cor->nome ?? '-' }}</td>
                        <td>{{ $controle->retro->nome ?? '-' }}</td>
                        <td>{{ $controle->usado->nome ?? '-' }}</td>
                        <td>{{ $controle->edicaoEspecial->nome ?? '-' }}</td>
                        <td>
                            <a href="{{ route('controle.show', $controle->id) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('controle.edit', $controle->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('controle.destroy', $controle->id) }}" method="post"
                                  class="d-inline" onsubmit="return confirm('Excluir este controle?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">Nenhum controle cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection