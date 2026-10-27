{{--
    Busca pelo código de barras no topo das listagens.
    O leitor de código de barras funciona como um teclado: "digita" os números
    e aperta Enter, então é só clicar no campo e bipar.

    Parâmetros:
      $tipo  'console', 'controle', 'jogo' ou 'acessorio' (prefixo das rotas)
--}}
<form method="GET" action="{{ route($tipo . '.index') }}" class="d-flex gap-1" role="search">
    <input type="text" name="codigo_barras" inputmode="numeric" maxlength="14" class="form-control"
           placeholder="Código de barras" aria-label="Buscar por código de barras"
           value="{{ request('codigo_barras') }}">
    <button type="submit" class="btn btn-outline-light" title="Buscar pelo código de barras">
        <i class="bi bi-upc-scan"></i>
    </button>
    @if (request()->filled('codigo_barras'))
        <a href="{{ route($tipo . '.index') }}" class="btn btn-outline-secondary" title="Limpar busca">&times;</a>
    @endif
</form>
