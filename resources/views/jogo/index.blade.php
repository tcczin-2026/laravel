@extends('layouts.app')

@section('titulo', 'Jogos')

@section('conteudo')
    @php
        // Parâmetro que controla, via PHP, se o painel de filtro aparece ou não.
        $mostrarFiltro = request()->boolean('mostrarFiltro');

        $filtrosAtivos = collect(request()->only(['nome', 'plataformajogo', 'estado', 'edicoes', 'retros']))
            ->filter(fn($v) => filled($v))
            ->count();

        // Monta a URL para abrir o painel, preservando os filtros já aplicados.
        $urlAbrirFiltro = request()->fullUrlWithQuery(['mostrarFiltro' => 1]);

        // Monta a URL para fechar o painel, removendo só o parâmetro mostrarFiltro.
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

    {{-- PAINEL DE FILTRO (controlado 100% via PHP, sem JS) --}}
    @if ($mostrarFiltro)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-funnel"></i> Filtrar jogos</span>
                <a href="{{ $urlFecharFiltro }}" class="btn-close btn-close-white" aria-label="Fechar"></a>
            </div>

            <form method="GET" action="{{ route('jogo.index') }}">
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
                            <select name="plataformajogo" class="form-select">
                                <option value="">Todas as plataformas</option>
                                @foreach ($plataformas as $plataforma)
                                    <option value="{{ $plataforma->id }}" @selected(request('plataformajogo') == $plataforma->id)>
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
                    <a href="{{ route('jogo.index', ['mostrarFiltro' => 1]) }}" class="btn btn-outline-light">Limpar filtros</a>
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
                            <a href="{{ route('jogo.show', $jogo->id) }}" class="btn btn-sm btn-info">Ver</a>
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
@endsection