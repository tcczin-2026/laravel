@extends('layouts.app')

@section('titulo', 'Controles')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Controles</h1>
        <a href="{{ route('controle.create') }}" class="btn btn-primary">Novo controle</a>
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
