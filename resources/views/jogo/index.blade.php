@extends('layouts.app')

@section('titulo', 'Jogos')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Jogos</h1>
        <a href="{{ route('jogo.create') }}" class="btn btn-primary">Novo jogo</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped bg-white align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Plataforma</th>
                    <th>Qtd.</th>
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
                        <td>{{ $jogo->console->nome ?? '-' }}</td>
                        <td>{{ $jogo->quantidade }}</td>
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
                        <td colspan="8" class="text-center text-muted">Nenhum jogo cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
