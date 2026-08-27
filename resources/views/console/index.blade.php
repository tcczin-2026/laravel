@extends('layouts.app')

@section('titulo', 'Consoles')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Consoles</h1>
        <a href="{{ route('console.create') }}" class="btn btn-primary">Novo console</a>
    </div>

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
