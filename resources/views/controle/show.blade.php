@extends('layouts.app')

@section('titulo', $controle->nome)

@section('conteudo')
    <h1 class="h3 mb-4">{{ $controle->nome }}</h1>

    <div class="card card-body mb-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">plataforma</dt>          <dd class="col-sm-9">{{ $controle->plataforma->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Quantidade</dt>     <dd class="col-sm-9">{{ $controle->quantidade }}</dd>
            <dt class="col-sm-3">Estado</dt>         <dd class="col-sm-9">{{ $controle->usado->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Cor</dt>            <dd class="col-sm-9">{{ $controle->cor->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Vintage</dt>        <dd class="col-sm-9">{{ $controle->retro->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Edicao especial</dt><dd class="col-sm-9">{{ $controle->edicaoEspecial->nome ?? '-' }}</dd>
        </dl>
    </div>

    <a href="{{ route('controle.edit', $controle->id) }}" class="btn btn-warning">Editar</a>
    <a href="{{ route('controle.index') }}" class="btn btn-secondary">Voltar</a>
@endsection
