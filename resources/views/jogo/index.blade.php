@extends('layouts.app')

@section('titulo', 'Jogos')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Jogos</h1>
        <a href="{{ route('jogo.create') }}" class="btn btn-primary">Novo jogo</a>
    </div>

    {{-- FORMULÁRIO DE FILTRO --}}
    <form method="GET" action="{{ route('jogo.index') }}" class="row g-2 mb-4">
        <div class="col-md-3">
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

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
            <a href="{{ route('jogo.index') }}" class="btn btn-outline-light flex-fill">Limpar</a>
        </div>
    </form>
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
                        <td>{{ $jogo->jogo->nome ?? '-' }}</td>
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
