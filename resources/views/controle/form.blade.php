@extends('layouts.app')

@section('titulo', 'Controle')

@section('conteudo')
    <h1 class="h3 mb-4">{{ $controle ? 'Editar controle' : 'Novo controle' }}</h1>

    <form method="post"
          action="{{ $controle ? route('controle.update', $controle->id) : route('controle.store') }}"
          class="card card-body bg-white">
        @csrf
        @if ($controle)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" maxlength="100" required
                       class="form-control" value="{{ old('nome', $controle->nome ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" min="0" required
                       class="form-control" value="{{ old('quantidade', $controle->quantidade ?? 1) }}">
            </div>

            @include('partials.select', ['campo' => 'MarcaControle', 'rotulo' => 'Marca',          'opcoes' => $marcas,   'selecionado' => $controle->MarcaControle ?? null])
            @include('partials.select', ['campo' => 'dispositivo',   'rotulo' => 'Dispositivo',    'opcoes' => $consoles, 'selecionado' => $controle->dispositivo ?? null])
            @include('partials.select', ['campo' => 'estado',        'rotulo' => 'Estado',         'opcoes' => $usados,   'selecionado' => $controle->estado ?? null])
            @include('partials.select', ['campo' => 'cores',         'rotulo' => 'Cor',            'opcoes' => $cores,    'selecionado' => $controle->cores ?? null])
            @include('partials.select', ['campo' => 'vintage',       'rotulo' => 'Vintage',        'opcoes' => $retros,   'selecionado' => $controle->vintage ?? null])
            @include('partials.select', ['campo' => 'colecionador',  'rotulo' => 'Edicao especial','opcoes' => $edicoes,  'selecionado' => $controle->colecionador ?? null])
        </div>

        <div>
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('controle.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection
