@extends('layouts.app')

@section('titulo', $jogo->nome)

@section('conteudo')
    <h1 class="h3 mb-4">{{ $jogo->nome }}</h1>

    <div class="card card-body mb-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Plataforma</dt>     <dd class="col-sm-9">{{ $jogo->console->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Quantidade</dt>     <dd class="col-sm-9">{{ $jogo->quantidade }}</dd>
            <dt class="col-sm-3">Estado</dt>         <dd class="col-sm-9">{{ $jogo->usado->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Vintage</dt>        <dd class="col-sm-9">{{ $jogo->retro->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Edicao especial</dt><dd class="col-sm-9">{{ $jogo->edicaoEspecial->nome ?? '-' }}</dd>
        </dl>
    </div>

    <a href="{{ route('jogo.edit', $jogo->id) }}" class="btn btn-warning">Editar</a>
    <a href="{{ route('jogo.index') }}" class="btn btn-secondary">Voltar</a>
@endsection
