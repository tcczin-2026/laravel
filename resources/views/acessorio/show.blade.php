@extends('layouts.app')

@section('titulo', $acessorio->nome)

@section('conteudo')
    <h1 class="h3 mb-4">{{ $acessorio->nome }}</h1>

    <div class="card card-body mb-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Nome</dt>       <dd class="col-sm-9">{{ $acessorio->nome }}</dd>
            <dt class="col-sm-3">Quantidade</dt> <dd class="col-sm-9">{{ $acessorio->quantidade ?? '-' }}</dd>
            <dt class="col-sm-3">Estado</dt>     <dd class="col-sm-9">{{ $acessorio->usado->nome ?? '-' }}</dd>
            <dt class="col-sm-3">Cor</dt>        <dd class="col-sm-9">{{ $acessorio->cor->nome ?? '-' }}</dd>
        </dl>
    </div>

    {{-- Histórico de alterações --}}
    @include('partials.historico', ['historico' => $acessorio->historico])

    <a href="{{ route('acessorio.edit', $acessorio->id) }}" class="btn btn-warning">
        Editar
    </a>

    <a href="{{ route('acessorio.index') }}" class="btn btn-secondary">
        Voltar
    </a>
@endsection