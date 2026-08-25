@extends('layouts.app')

@section('titulo', $titulo)

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ $titulo }}</h1>
        <a href="{{ route('auxiliar.create', $tipo) }}" class="btn btn-primary">Novo</a>
    </div>

    <table class="table table-striped bg-white align-middle">
        <thead>
            <tr>
                <th style="width: 80px">ID</th>
                <th>Nome</th>
                <th style="width: 180px">Acoes</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registros as $registro)
                <tr>
                    <td>{{ $registro->id }}</td>
                    <td>{{ $registro->nome }}</td>
                    <td>
                        <a href="{{ route('auxiliar.edit', [$tipo, $registro->id]) }}"
                           class="btn btn-sm btn-warning">Editar</a>

                        <form action="{{ route('auxiliar.destroy', [$tipo, $registro->id]) }}"
                              method="post" class="d-inline"
                              onsubmit="return confirm('Excluir este registro?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Nenhum registro cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ route('painel.index') }}" class="btn btn-link">Voltar ao painel</a>
@endsection
