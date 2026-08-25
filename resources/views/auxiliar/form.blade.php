@extends('layouts.app')

@section('titulo', $titulo)

@section('conteudo')
    <h1 class="h3 mb-4">{{ $registro ? 'Editar' : 'Novo' }} - {{ $titulo }}</h1>

    <form method="post"
          action="{{ $registro
              ? route('auxiliar.update', [$tipo, $registro->id])
              : route('auxiliar.store', $tipo) }}"
          class="card card-body">
        @csrf
        @if ($registro)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" maxlength="20" required
                   class="form-control" value="{{ old('nome', $registro->nome ?? '') }}">
        </div>

        <div>
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('auxiliar.index', $tipo) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection
