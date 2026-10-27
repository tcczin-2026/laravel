@extends('layouts.app')

@section('titulo', 'Console')

@section('conteudo')
    <h1 class="h3 mb-4">{{ $console ? 'Editar console' : 'Novo console' }}</h1>

    <form method="post"
          action="{{ $console ? route('console.update', $console->id) : route('console.store') }}"
        enctype="multipart/form-data"  
        class="card card-body">
        @csrf
        @if ($console)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-5 mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" maxlength="100" required
                       class="form-control" value="{{ old('nome', $console->nome ?? '') }}">
            </div>

            <div class="col-md-2 mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" min="0" required
                       class="form-control" value="{{ old('quantidade', $console->quantidade ?? 1) }}">
            </div>
            <div class="col-md-2 mb-3">
                <label for="valor" class="form-label">Valor (R$)</label>
                <input type="number" name="valor" id="valor" min="1" step="0.01" required
                       class="form-control" placeholder="0,00"
                       value="{{ old('valor', $console->valor ?? '') }}">
                @error('valor')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="codigo_barras" class="form-label">Código de barras</label>
                <input type="text" name="codigo_barras" id="codigo_barras" inputmode="numeric"
                       pattern="[0-9]{8,14}" maxlength="14" class="form-control" placeholder="ex: 7891234567895"
                       title="De 8 a 14 números (EAN/UPC)"
                       value="{{ old('codigo_barras', $console->codigo_barras ?? '') }}">
                @error('codigo_barras')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 mb-3">
                <label for="armazenamento" class="form-label">Armazenamento (GB)</label>
                <input type="number" name="armazenamento" id="armazenamento" min="1" step="1" required
                       class="form-control" placeholder="ex: 825 ou 1000"
                       value="{{ old('armazenamento', $console->armazenamento ?? '') }}">
                @error('armazenamento')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label for="imagem" class="form-label">Imagem</label>
                @if ($console && $console->imagem_url)
                    <div class="mb-2">
                        <img src="{{ $console->imagem_url }}" alt="{{ $console->nome }}"
                            style="max-height: 120px; min-height: 70px;" class="rounded">
                    </div>
                @endif
                <input type="file" name="imagem" id="imagem" accept="image/*" class="form-control">
                @error('imagem')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            @include('partials.select', ['campo' => 'plataformaConsole', 'rotulo' => 'plataforma',        'opcoes' => $plataformas,        'selecionado' => $console->plataformaConsole ?? null])
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
