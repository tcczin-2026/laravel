@extends('layouts.app')

@section('titulo', 'Jogo')

@section('conteudo')
    <h1 class="h3 mb-4">{{ $jogo ? 'Editar jogo' : 'Novo jogo' }}</h1>

    <form method="post"
          action="{{ $jogo ? route('jogo.update', $jogo->id) : route('jogo.store') }}"
          enctype="multipart/form-data"
          class="card card-body">
        @csrf
        @if ($jogo)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" maxlength="100" required
                       class="form-control" value="{{ old('nome', $jogo->nome ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" min="0" required
                       class="form-control" value="{{ old('quantidade', $jogo->quantidade ?? 1) }}">
            </div>

            <div class="col-md-12 mb-3">
                <label for="imagem" class="form-label">Imagem</label>
                @if ($jogo && $jogo->imagem_url)
                    <div class="mb-2">
                        <img src="{{ $jogo->imagem_url }}" alt="{{ $jogo->nome }}"
                            style="max-height: 120px; min-height: 70px;" class="rounded">
                    </div>
                @endif
                <input type="file" name="imagem" id="imagem" accept="image/*" class="form-control">
                @error('imagem')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            @include('partials.select', ['campo' => 'Plataforma',   'rotulo' => 'Plataforma',     'opcoes' => $plataformas, 'selecionado' => $jogo->Plataforma ?? null])
            @include('partials.select', ['campo' => 'estado',       'rotulo' => 'Estado',         'opcoes' => $usados,   'selecionado' => $jogo->estado ?? null])
            @include('partials.select', ['campo' => 'vintage',      'rotulo' => 'Vintage',        'opcoes' => $retros,   'selecionado' => $jogo->vintage ?? null])
            @include('partials.select', ['campo' => 'colecionador', 'rotulo' => 'Edicao especial','opcoes' => $edicoes,  'selecionado' => $jogo->colecionador ?? null])
        </div>

        <div>
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('jogo.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection
