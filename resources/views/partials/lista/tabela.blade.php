{{--
    Tabela das listagens principais (console, controle, jogo, acessório).
    Feita pra caber na tela sem barra de rolagem lateral:
      - celular:   Imagem (menor) | Nome (+ valor e estoque embaixo) | Ações
      - tablet:    + Estoque e Valor em colunas próprias
      - notebook:  + Características (estado, cor, retrô... como etiquetas que quebram linha)
    Tudo continua no botão "Ver".

    Parâmetros:
      $itens           paginator com os registros
      $tipo            'console', 'controle', 'jogo' ou 'acessorio' (prefixo das rotas)
      $artigo          'este console', 'este jogo'... (mensagem de confirmação ao excluir)
      $vazio           texto quando não há nada cadastrado
      $caracteristicas fn ($item) => ['rótulo' => 'valor', ...]
--}}
<div class="table-responsive">
    <table class="table table-dark align-middle tabela-lista">
        <thead>
            <tr>
                <th class="col-img"></th>
                <th>Nome</th>
                <th class="d-none d-md-table-cell">Valor</th>
                <th class="d-none d-sm-table-cell">Estoque</th>
                <th class="d-none d-xl-table-cell">Características</th>
                <th class="col-acoes">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($itens as $item)
                <tr>
                    <td class="col-img">
                        @if ($item->imagem_url)
                            <img src="{{ $item->imagem_url }}" alt="{{ $item->nome }}" class="lista-img">
                        @else
                            <div class="lista-img lista-img--vazia">&#127918;</div>
                        @endif
                    </td>

                    <td class="col-nome">
                        <div class="fw-semibold">{{ $item->nome }}</div>
                        <div class="small text-muted">#{{ $item->id }} · {{ $item->plataforma->nome ?? '-' }}</div>
                        {{-- no celular a coluna Valor some, então o preço vem pra cá --}}
                        @if ($item->valor !== null)
                            <div class="small d-md-none">{{ $item->valor_formatado }}</div>
                        @endif
                        {{-- no celular a coluna Estoque some, então a etiqueta vem pra cá --}}
                        <div class="d-sm-none mt-1">
                            @include('partials.lista.estoque', ['quantidade' => $item->quantidade])
                        </div>
                    </td>

                    <td class="d-none d-md-table-cell text-nowrap">{{ $item->valor_formatado }}</td>

                    <td class="d-none d-sm-table-cell">
                        @include('partials.lista.estoque', ['quantidade' => $item->quantidade])
                    </td>

                    <td class="d-none d-xl-table-cell">
                        <div class="chips">
                            @foreach ($caracteristicas($item) as $rotulo => $valor)
                                @if (filled($valor) && $valor !== '-')
                                    <span class="chip" title="{{ $rotulo }}">{{ $valor }}</span>
                                @endif
                            @endforeach
                        </div>
                    </td>

                    {{-- Botões empilhados numa coluna estreita --}}
                    <td class="col-acoes">
                        <div class="acoes">
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#verModal{{ $item->id }}">Ver</button>
                            <a href="{{ route($tipo . '.edit', $item->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route($tipo . '.destroy', $item->id) }}" method="post"
                                  onsubmit="return confirm(@js('Excluir ' . $artigo . '?'))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">{{ $vazio }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
