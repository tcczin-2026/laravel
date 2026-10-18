{{--
    Botões de reverter de uma linha do histórico.

    Como usar (dentro do @foreach do histórico, ordenado do mais novo pro mais antigo):
        @include('partials.historico-acoes', ['tipo' => 'jogo', 'item' => $item, 'ultimo' => $loop->first])

    - Desfazer: volta só este campo para o valor "De".
    - Restaurar até aqui: desfaz tudo que foi alterado depois desta linha.
--}}
<div class="acoes">
    @if (in_array($item->acao, ['atualizado', 'revertido']) && $item->campo && $item->campo !== 'imagem')
        <form action="{{ route('historico.desfazer', [$tipo, $item->id]) }}" method="post"
              onsubmit="return confirm(@js('Voltar "' . $item->campo_rotulo . '" para "' . ($item->de_texto ?? 'vazio') . '"?'))">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning" title="Volta este campo para o valor anterior">Desfazer</button>
        </form>
    @endif

    @if (! $ultimo && $item->acao !== 'excluido')
        <form action="{{ route('historico.restaurar', [$tipo, $item->id]) }}" method="post"
              onsubmit="return confirm(@js('Desfazer todas as alterações feitas depois de ' . $item->created_at?->format('d/m/Y H:i') . '?'))">
            @csrf
            <button type="submit" class="btn btn-sm btn-info" title="Desfaz tudo que foi alterado depois desta linha">Restaurar até aqui</button>
        </form>
    @endif
</div>
