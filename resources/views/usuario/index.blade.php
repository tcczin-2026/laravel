@extends('layouts.app')

@section('titulo', 'Usuários')

@section('conteudo')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 mb-0">Usuários</h1>
        <a href="{{ route('usuario.create') }}" class="btn btn-primary">Novo usuário</a>
    </div>

    <div class="table-responsive">
        <table class="table table-dark align-middle">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th style="width: 180px">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>
                            {{ $usuario->name }}
                            @if ($usuario->id === auth()->id())
                                <span class="text-muted">(você)</span>
                            @endif
                        </td>
                        <td>{{ $usuario->email }}</td>
                        <td>
                            @if ($usuario->isGerente())
                                <span class="badge bg-primary">Gerente</span>
                            @else
                                <span class="badge bg-secondary">Funcionário</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('usuario.edit', $usuario->id) }}" class="btn btn-sm btn-warning">Editar</a>

                            @if ($usuario->id !== auth()->id())
                                <form action="{{ route('usuario.destroy', $usuario->id) }}" method="post"
                                      class="d-inline" onsubmit="return confirm('Excluir este usuário?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhum usuário cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="paginacao mt-3">
        {{ $usuarios->links() }}
    </div>
@endsection
