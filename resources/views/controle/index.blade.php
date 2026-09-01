@extends('layouts.app')

@section('titulo', 'Controles')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Controles</h1>
        <a href="{{ route('controle.create') }}" class="btn btn-primary">Novo controle</a>
    </div>

    {{-- FORMULÁRIO DE FILTRO --}}
    <form method="GET" action="{{ route('controle.index') }}" class="row g-2 mb-4">
        <div class="col-md-3">
            <input type="text" name="nome" class="form-control" placeholder="Buscar por nome"
                   value="{{ request('nome') }}">
        </div>

        <div class="col-md-2">
            <select name="plataformacontrole" class="form-select">
                <option value="">Todas as plataformas</option>
                @foreach ($plataformas as $plataforma)
                    <option value="{{ $plataforma->id }}" @selected(request('plataformacontrole') == $plataforma->id)>
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

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
            <a href="{{ route('controle.index') }}" class="btn btn-outline-light flex-fill">Limpar</a>
        </div>
    </form>
    {{-- FIM DO FILTRO --}}

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
