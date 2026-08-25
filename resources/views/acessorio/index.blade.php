@extends('layouts.app')

@section('titulo', 'Acessorios')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Acessorios</h1>
        <a href="{{ route('acessorio.create') }}" class="btn btn-primary">Novo acessorio</a>
    </div>

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
                        <td>{{ $acessorio->console->nome ?? '-' }}</td>
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
                            <a href="{{ route('acessorio.show', $acessorio->id) }}" class="btn btn-sm btn-info">Ver</a>
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
@endsection
