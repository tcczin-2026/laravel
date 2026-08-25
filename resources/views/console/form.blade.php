@extends('layouts.app')

@section('titulo', 'Console')

@section('conteudo')
    <h1 class="h3 mb-4">{{ $console ? 'Editar console' : 'Novo console' }}</h1>

    <form method="post"
          action="{{ $console ? route('console.update', $console->id) : route('console.store') }}"
          class="card card-body bg-white">
        @csrf
        @if ($console)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" maxlength="100" required
                       class="form-control" value="{{ old('nome', $console->nome ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" min="0" required
                       class="form-control" value="{{ old('quantidade', $console->quantidade ?? 1) }}">
            </div>

            @include('partials.select', ['campo' => 'MarcaConsole', 'rotulo' => 'Marca',        'opcoes' => $marcas,        'selecionado' => $console->MarcaConsole ?? null])
            @include('partials.select', ['campo' => 'estado',       'rotulo' => 'Estado',       'opcoes' => $usados,        'selecionado' => $console->estado ?? null])
            @include('partials.select', ['campo' => 'leitor',       'rotulo' => 'Leitor',       'opcoes' => $digitais,      'selecionado' => $console->leitor ?? null])
            @include('partials.select', ['campo' => 'cores',        'rotulo' => 'Cor',          'opcoes' => $cores,         'selecionado' => $console->cores ?? null])
            @include('partials.select', ['campo' => 'vintage',      'rotulo' => 'Vintage',      'opcoes' => $retros,        'selecionado' => $console->vintage ?? null])
            @include('partials.select', ['campo' => 'aberto',       'rotulo' => 'Desbloqueado', 'opcoes' => $desbloqueados, 'selecionado' => $console->aberto ?? null])
            @include('partials.select', ['campo' => 'colecionador', 'rotulo' => 'Edicao especial', 'opcoes' => $edicoes,    'selecionado' => $console->colecionador ?? null])
        </div>

        <div>
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('console.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection
