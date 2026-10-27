@extends('layouts.app')

@section('titulo', 'Colecao de Games')

@section('conteudo')
    <h1 class="h3 mb-4">Colecao de Games</h1>

    <h5 class="text-muted">Cadastros principais</h5>
    <div class="row g-3 mb-5">
        @foreach ($totais as $rota => $total)
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-capitalize">{{ $rota }}</h5>
                        <p class="display-6 mb-0">{{ $total }}</p>
                        <div class="small text-muted mb-2">em estoque</div>
                        <a href="{{ route($rota . '.index') }}" class="btn btn-primary btn-sm">Gerenciar</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if (auth()->user()->isGerente())
        <h5 class="text-muted">Tabelas auxiliares</h5>
        <div class="row g-3">
            @foreach ($auxiliares as $tipo => $config)
                <div class="col-6 col-md-3">
                    <a href="{{ route('auxiliar.index', $tipo) }}"
                       class="btn btn-secondary w-100">{{ $config['titulo'] }}</a>
                </div>
            @endforeach
        </div>
    @endif

    <h5 class="text-muted mt-5 mb-3">Últimas alterações</h5>

    <form method="GET" action="{{ route('painel.index') }}" class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <input type="number" name="id" min="1" class="form-control" placeholder="ID do item"
                   value="{{ request('id') }}">
        </div>
        <div class="col-6 col-md-3">
            <select name="tipo" class="form-select">
                <option value="">Todos os tipos</option>
                @foreach ($tiposHistorico as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Buscar</button>
            @if (request()->filled('id') || request()->filled('tipo'))
                <a href="{{ route('painel.index') }}" class="btn btn-outline-secondary">Limpar</a>
            @endif
        </div>
    </form>

    @if ($historico->isEmpty())
        <div class="card card-body text-muted">
            {{ request()->filled('id') || request()->filled('tipo') ? 'Nenhuma alteração encontrada para essa busca.' : 'Nenhuma alteração registrada ainda.' }}
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-dark align-middle">
                <thead>
                    <tr>
                        <th>Quando</th>
                        <th>Item</th>
                        <th>Ação</th>
                        <th class="d-none d-md-table-cell">Alteração</th>
                        <th class="d-none d-sm-table-cell">Usuário</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($historico as $item)
                        <tr>
                            <td class="text-nowrap">
                                {{ $item['quando']->format('d/m/Y H:i') }}
                                <div class="small text-muted">{{ $item['quando']->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $item['tipo'] }} #{{ $item['id'] }}</div>
                                @if ($item['link'])
                                    <a href="{{ $item['link'] }}">{{ $item['nome'] }}</a>
                                @else
                                    {{ $item['nome'] }}
                                @endif
                            </td>
                            <td>
                                {{-- mesmas cores do histórico dentro dos modais --}}
                                @switch($item['acao'])
                                    @case('criado')
                                        <span class="badge bg-success">Criado</span>
                                        @break
                                    @case('revertido')
                                        <span class="badge bg-info text-dark">Revertido</span>
                                        @break
                                    @case('excluido')
                                        <span class="badge bg-danger">Excluído</span>
                                        @break
                                    @default
                                        <span class="badge bg-warning text-dark">Atualizado</span>
                                @endswitch
                            </td>
                            <td class="d-none d-md-table-cell">
                                {{-- 'criado'/'excluido' guardam um resumo do item inteiro, não um campo --}}
                                @if ($item['campo'])
                                    <span class="text-muted">{{ $item['campo'] }}:</span>
                                    {{ Str::limit($item['de'] ?? 'vazio', 30) }}
                                    <span class="text-muted">&rarr;</span>
                                    {{ Str::limit($item['para'] ?? 'vazio', 30) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-sm-table-cell">{{ $item['usuario'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="paginacao mt-3">
            {{ $historico->links() }}
        </div>
    @endif
@endsection
