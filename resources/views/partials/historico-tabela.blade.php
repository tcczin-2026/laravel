{{--
    Tabela de histórico de um item (usada no modal "Ver" e na página de detalhes).
    3 colunas que cabem até no celular, sem barra de rolagem lateral:
      Quando (data + usuário) | Alteração (ação + "campo: de -> para") | Reverter (botões empilhados)

    Uso:
        @include('partials.historico-tabela', ['historico' => $console->historico, 'tipo' => 'console'])
    Sem 'tipo', a coluna Reverter não aparece.
--}}
@if ($historico->isEmpty())
    <p class="text-muted mb-0">Nenhuma alteração registrada.</p>
@else
    <div class="table-responsive">
        <table class="table table-dark table-sm align-middle mb-0 tabela-historico">
            <thead>
                <tr>
                    <th>Quando</th>
                    <th>Alteração</th>
                    @isset($tipo)
                        <th class="col-acoes">Reverter</th>
                    @endisset
                </tr>
            </thead>
            <tbody>
                @foreach ($historico->sortByDesc('id') as $item)
                    <tr>
                        <td class="text-nowrap">
                            {{ $item->created_at?->format('d/m/Y H:i') }}
                            <div class="small text-muted">{{ $item->usuario ?? 'Sistema' }}</div>
                        </td>

                        <td>
                            @switch($item->acao)
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

                            @if ($item->campo)
                                {{-- nomes em vez de ids (trait FormataHistorico) --}}
                                <div class="mt-1">
                                    <span class="text-muted">{{ $item->campo_rotulo }}:</span>
                                    {{ $item->de_texto ?? 'vazio' }}
                                    <span class="text-muted">&rarr;</span>
                                    {{ $item->para_texto ?? 'vazio' }}
                                </div>
                            @elseif ($item->valor_novo ?? $item->valor_anterior)
                                {{-- criado/excluído guardam um resumo do item inteiro --}}
                                <div class="mt-1 small text-muted">{{ $item->valor_novo ?? $item->valor_anterior }}</div>
                            @endif
                        </td>

                        @isset($tipo)
                            <td class="col-acoes">
                                @include('partials.historico-acoes', ['item' => $item, 'ultimo' => $loop->first])
                            </td>
                        @endisset
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
