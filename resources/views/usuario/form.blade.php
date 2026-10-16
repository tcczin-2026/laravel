@extends('layouts.app')

@section('titulo', 'Usuários')

@section('conteudo')
    <h1 class="h3 mb-4">{{ $usuario ? 'Editar' : 'Novo' }} usuário</h1>

    <form method="post"
          action="{{ $usuario ? route('usuario.update', $usuario->id) : route('usuario.store') }}"
          class="card card-body">
        @csrf
        @if ($usuario)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nome</label>
                <input type="text" name="name" id="name" maxlength="100" required
                       class="form-control" value="{{ old('name', $usuario->name ?? '') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">E-mail (usado no login)</label>
                <input type="email" name="email" id="email" required
                       class="form-control" value="{{ old('email', $usuario->email ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="perfil" class="form-label">Perfil</label>
                <select name="perfil" id="perfil" class="form-select" required>
                    @foreach (\App\Models\User::PERFIS as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected(old('perfil', $usuario->perfil ?? 'funcionario') === $valor)>
                            {{ $rotulo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" name="password" id="password" minlength="6"
                       class="form-control" @required(! $usuario)
                       placeholder="{{ $usuario ? 'Deixe em branco para manter' : 'Mínimo 6 caracteres' }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="password_confirmation" class="form-label">Confirmar senha</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control" @required(! $usuario)>
            </div>
        </div>

        <p class="text-muted small">
            <strong>Gerente:</strong> acessa tudo, inclusive usuários e tabelas auxiliares.<br>
            <strong>Funcionário:</strong> acessa só consoles, controles, jogos e acessórios.
        </p>

        <div>
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('usuario.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection
