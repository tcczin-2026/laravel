@extends('layouts.app')

@section('titulo', 'Acessorio')

@section('conteudo')
    <h1 class="h3 mb-4">{{ $acessorio ? 'Editar acessorio' : 'Novo acessorio' }}</h1>

    <form method="post"
          action="{{ $acessorio ? route('acessorio.update', $acessorio->id) : route('acessorio.store') }}"
          class="card card-body bg-white">
        @csrf
        @if ($acessorio)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" maxlength="100" required
                       class="form-control" value="{{ old('nome', $acessorio->nome ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" min="0" required
                       class="form-control" value="{{ old('quantidade', $acessorio->quantidade ?? 1) }}">
            </div>

            @include('partials.select', ['campo' => 'Plataforma',   'rotulo' => 'Plataforma',     'opcoes' => $consoles, 'selecionado' => $acessorio->Plataforma ?? null])
            @include('partials.select', ['campo' => 'estado',       'rotulo' => 'Estado',         'opcoes' => $usados,   'selecionado' => $acessorio->estado ?? null])
            @include('partials.select', ['campo' => 'cores',        'rotulo' => 'Cor',            'opcoes' => $cores,    'selecionado' => $acessorio->cores ?? null])
            @include('partials.select', ['campo' => 'vintage',      'rotulo' => 'Vintage',        'opcoes' => $retros,   'selecionado' => $acessorio->vintage ?? null])
            @include('partials.select', ['campo' => 'colecionador', 'rotulo' => 'Edicao especial','opcoes' => $edicoes,  'selecionado' => $acessorio->colecionador ?? null])
        </div>

        <div>
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('acessorio.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection
