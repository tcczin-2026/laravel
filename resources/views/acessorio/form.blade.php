@extends('layouts.app')

@section('titulo', 'Acessorio')

@section('conteudo')
    <form method="post"
          action="{{ $acessorio ? route('acessorio.update', $acessorio->id) : route('acessorio.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if ($acessorio)
            @method('PUT')
        @endif

        {{-- CABEÇALHO: título + ações --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <div class="form-breadcrumb">
                    <a href="{{ route('acessorio.index') }}">Acessorios</a>
                    <span>›</span>
                    {{ $acessorio ? 'Editar' : 'Novo' }}
                </div>
                <h1 class="h3 mb-0">{{ $acessorio ? $acessorio->nome : 'Novo acessorio' }}</h1>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('acessorio.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </div>

        <div class="row g-4">
            {{-- COLUNA ESQUERDA: imagem --}}
            <div class="col-lg-4">
                <div class="card card-body form-section">
                    <h2 class="form-section-title">Imagem</h2>

                    <div class="imagem-preview mb-3">
                        <img id="imagemPreview"
                             src="{{ $acessorio && $acessorio->imagem_url ? $acessorio->imagem_url : '' }}"
                             alt="{{ $acessorio->nome ?? 'Pré-visualização' }}"
                             class="{{ $acessorio && $acessorio->imagem_url ? '' : 'd-none' }}">
                        <span id="imagemVazia" class="{{ $acessorio && $acessorio->imagem_url ? 'd-none' : '' }}">
                            Sem imagem
                        </span>
                    </div>

                    <input type="file" name="imagem" id="imagem" accept="image/*" class="form-control">
                    <div class="form-text">JPG, PNG ou WEBP, até 2 MB.</div>
                    @error('imagem')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- COLUNA DIREITA: campos em seções --}}
            <div class="col-lg-8 d-flex flex-column gap-4">

                <div class="card card-body form-section">
                    <h2 class="form-section-title">Informações básicas</h2>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" name="nome" id="nome" maxlength="100" required
                                   class="form-control" value="{{ old('nome', $acessorio->nome ?? '') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="codigo_barras" class="form-label">Código de barras</label>
                            <input type="text" name="codigo_barras" id="codigo_barras" inputmode="numeric"
                                   pattern="[0-9]{8,14}" maxlength="14" class="form-control" placeholder="ex: 7891234567895"
                                   title="De 8 a 14 números (EAN/UPC)"
                                   value="{{ old('codigo_barras', $acessorio->codigo_barras ?? '') }}">
                            @error('codigo_barras')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        @include('partials.select', ['campo' => 'Plataforma', 'rotulo' => 'Plataforma', 'opcoes' => $plataformas, 'selecionado' => $acessorio->Plataforma ?? null])

                    </div>
                </div>

                <div class="card card-body form-section">
                    <h2 class="form-section-title">Características</h2>
                    <div class="row">
                        @include('partials.select', ['campo' => 'estado', 'rotulo' => 'Estado', 'opcoes' => $usados, 'selecionado' => $acessorio->estado ?? null])
                        @include('partials.select', ['campo' => 'cores', 'rotulo' => 'Cor', 'opcoes' => $cores, 'selecionado' => $acessorio->cores ?? null])
                        @include('partials.select', ['campo' => 'vintage', 'rotulo' => 'Vintage', 'opcoes' => $retros, 'selecionado' => $acessorio->vintage ?? null])
                        @include('partials.select', ['campo' => 'colecionador', 'rotulo' => 'Edicao especial', 'opcoes' => $edicoes, 'selecionado' => $acessorio->colecionador ?? null])
                    </div>
                </div>

                <div class="card card-body form-section">
                    <h2 class="form-section-title">Estoque e valor</h2>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <input type="number" name="quantidade" id="quantidade" min="0" required
                                   class="form-control" value="{{ old('quantidade', $acessorio->quantidade ?? 1) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="valor" class="form-label">Valor (R$)</label>
                            <input type="number" name="valor" id="valor" min="1" step="0.01" required
                                   class="form-control" placeholder="0,00"
                                   value="{{ old('valor', $acessorio->valor ?? '') }}">
                            @error('valor')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    {{-- Pré-visualização da imagem escolhida (só visual, não envia nada) --}}
    <script>
        document.getElementById('imagem').addEventListener('change', function () {
            const arquivo = this.files[0];
            const img = document.getElementById('imagemPreview');
            const vazio = document.getElementById('imagemVazia');
            if (!arquivo) return;
            img.src = URL.createObjectURL(arquivo);
            img.classList.remove('d-none');
            vazio.classList.add('d-none');
        });
    </script>
@endsection