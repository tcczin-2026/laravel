@extends('layouts.app')

@section('titulo', 'Colecao de Games')

@section('conteudo')
    <h1 class="mb-4">Colecao de Games</h1>

    <h5 class="text-muted">Cadastros principais</h5>
    <div class="row g-3 mb-5">
        @foreach ($totais as $rota => $total)
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-capitalize">{{ $rota }}</h5>
                        <p class="display-6 mb-2">{{ $total }}</p>
                        <a href="{{ route($rota . '.index') }}" class="btn btn-primary btn-sm">Gerenciar</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <h5 class="text-muted">Tabelas auxiliares</h5>
    <div class="row g-3">
        @foreach ($auxiliares as $tipo => $config)
            <div class="col-md-3">
                <a href="{{ route('auxiliar.index', $tipo) }}"
                   class="btn btn-outline-secondary w-100">{{ $config['titulo'] }}</a>
            </div>
        @endforeach
    </div>
@endsection
