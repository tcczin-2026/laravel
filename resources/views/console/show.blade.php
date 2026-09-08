@extends('layouts.app')

@section('titulo', $console->nome)

@section('conteudo')
    <h1 class="h3 mb-4">{{ $console->nome }}</h1>

    <div class="card card-body mb-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">plataforma</dt>          <dd class="col-sm-9">{{ $console->plataforma->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Quantidade</dt>     <dd class="col-sm-9">{{ $console->quantidade }}</dd>
            <dt class="col-sm-3">Estado</dt>         <dd class="col-sm-9">{{ $console->usado->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Leitor</dt>         <dd class="col-sm-9">{{ $console->digital->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Cor</dt>            <dd class="col-sm-9">{{ $console->cor->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Vintage</dt>        <dd class="col-sm-9">{{ $console->retro->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Desbloqueado</dt>   <dd class="col-sm-9">{{ $console->desbloqueado->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Edicao especial</dt><dd class="col-sm-9">{{ $console->edicaoEspecial->nome ?? '-' }}</dd>
        </dl>
    </div>

    <div class="row">
        <div class="col-md-4">
            <h5>Controles ({{ $console->controles->count() }})</h5>
            <ul class="list-group mb-3">
                @forelse ($console->controles as $item)
                    <li class="list-group-item">{{ $item->nome }}</li>
                @empty
                    <li class="list-group-item text-muted">Nenhum.</li>
                @endforelse
            </ul>
        </div>
        <div class="col-md-4">
            <h5>Jogos ({{ $console->jogos->count() }})</h5>
            <ul class="list-group mb-3">
                @forelse ($console->jogos as $item)
                    <li class="list-group-item">{{ $item->nome }}</li>
                @empty
                    <li class="list-group-item text-muted">Nenhum.</li>
                @endforelse
            </ul>
        </div>
        <div class="col-md-4">
            <h5>Acessorios ({{ $console->acessorios->count() }})</h5>
            <ul class="list-group mb-3">
                @forelse ($console->acessorios as $item)
                    <li class="list-group-item">{{ $item->nome }}</li>
                @empty
                    <li class="list-group-item text-muted">Nenhum.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Bloco de histórico de alterações: resources/views/partials/historico.blade.php --}}
    @include('partials.historico', ['historico' => $console->historico])

    <a href="{{ route('console.edit', $console->id) }}" class="btn btn-warning">Editar</a>
    <a href="{{ route('console.index') }}" class="btn btn-secondary">Voltar</a>
@endsection