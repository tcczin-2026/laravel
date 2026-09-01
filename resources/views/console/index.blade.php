@extends('layouts.app')

@section('titulo', 'Consoles')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Consoles</h1>
        <a href="{{ route('console.create') }}" class="btn btn-primary">Novo console</a>
    </div>

    {{-- FORMULÁRIO DE FILTRO --}}
    <form method="GET" action="{{ route('console.index') }}" class="row g-2 mb-4">
        <div class="col-md-3">
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
                    <option value="{{ $digital->id }}" @selected(request('digitals') == $digital->id)>
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

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
            <a href="{{ route('console.index') }}" class="btn btn-outline-light flex-fill">Limpar</a>
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
                    <th>Estado</th>
                    <th>Leitor</th>
                    <th>Cor</th>
                    <th>Vintage</th>
                    <th>Desbloqueado</th>
                    <th>Edicao</th>
                    <th style="width: 220px">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($consoles as $console)
                    <tr>
                        <td>{{ $console->id }}</td>
                        <td>{{ $console->nome }}</td>
                        <td>{{ $console->plataforma->nome ?? '-' }}</td>
                        <td>{{ $console->quantidade }}</td>
                        <td>
                            @if ($console->quantidade > 0)
                                <span class="stock-pill stock-pill--ok">Em estoque</span>
                            @else
                                <span class="stock-pill stock-pill--out">Esgotado</span>
                            @endif
                        </td>
                        <td>{{ $console->usado->nome ?? '-' }}</td>
                        <td>{{ $console->digital->nome ?? '-' }}</td>
                        <td>{{ $console->cor->nome ?? '-' }}</td>
                        <td>{{ $console->retro->nome ?? '-' }}</td>
                        <td>{{ $console->desbloqueado->nome ?? '-' }}</td>
                        <td>{{ $console->edicaoEspecial->nome ?? '-' }}</td>
                        <td>
                            <a href="{{ route('console.show', $console->id) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('console.edit', $console->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('console.destroy', $console->id) }}" method="post"
                                  class="d-inline" onsubmit="return confirm('Excluir este console?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted">Nenhum console cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection