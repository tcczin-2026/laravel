{{--
    Paginação padrão do sistema (registrada no AppServiceProvider).
    - Números: primeira, última, a atual e uma de cada lado  ->  1 … 6 [7] 8 … 20
    - "Ir para": digita o número da página
    - "Itens por página": 10, 25, 50 ou 100 (?por_pagina=)
    Os filtros da tela (nome, plataforma...) são mantidos em tudo.
--}}
@php
    $atual  = $paginator->currentPage();
    $ultima = $paginator->lastPage();

    // 1, atual-1, atual, atual+1, última -> sem repetir e sem sair do intervalo
    $paginas = collect([1, $atual - 1, $atual, $atual + 1, $ultima])
        ->filter(fn ($p) => $p >= 1 && $p <= $ultima)
        ->unique()->sort()->values();

    $porPagina = $paginator->perPage();
    $opcoes    = \App\Http\Controllers\Controller::POR_PAGINA;
@endphp

{{-- Só aparece se houver mais itens do que a menor opção (senão não há o que paginar) --}}
@if ($paginator->total() > $opcoes[0])
    <nav class="d-flex align-items-center justify-content-between" aria-label="Paginação">

        <div class="d-flex align-items-center flex-wrap gap-3">
            <p class="small">
                Mostrando <span class="fw-semibold">{{ $paginator->firstItem() ?? 0 }}</span>
                a <span class="fw-semibold">{{ $paginator->lastItem() ?? 0 }}</span>
                de <span class="fw-semibold">{{ $paginator->total() }}</span> resultados
            </p>

            {{-- Itens por página: troca e volta pra página 1 --}}
            <form method="get" action="{{ url()->current() }}" class="pag-form">
                @foreach (request()->except(['page', 'por_pagina']) as $nome => $valor)
                    @if (is_scalar($valor))
                        <input type="hidden" name="{{ $nome }}" value="{{ $valor }}">
                    @endif
                @endforeach
                <label for="por_pagina" class="small">Itens por página</label>
                <select name="por_pagina" id="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($opcoes as $opcao)
                        <option value="{{ $opcao }}" @selected($opcao === $porPagina)>{{ $opcao }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="btn btn-sm btn-secondary">Ok</button></noscript>
            </form>
        </div>

        @if ($paginator->hasPages())
            <div class="d-flex align-items-center flex-wrap gap-3">
                <ul class="pagination">
                    {{-- Anterior --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">&lsaquo;</a></li>
                    @endif

                    @foreach ($paginas as $i => $pagina)
                        {{-- "…" quando há páginas escondidas entre um número e outro --}}
                        @if ($i > 0 && $pagina - $paginas[$i - 1] > 1)
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">&hellip;</span></li>
                        @endif

                        @if ($pagina === $atual)
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $pagina }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $paginator->url($pagina) }}">{{ $pagina }}</a></li>
                        @endif
                    @endforeach

                    {{-- Próxima --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Próxima">&rsaquo;</a></li>
                    @else
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                    @endif
                </ul>

                {{-- Ir para uma página específica --}}
                <form method="get" action="{{ url()->current() }}" class="pag-form">
                    @foreach (request()->except('page') as $nome => $valor)
                        @if (is_scalar($valor))
                            <input type="hidden" name="{{ $nome }}" value="{{ $valor }}">
                        @endif
                    @endforeach
                    <label for="ir_pagina" class="small">Ir para</label>
                    <input type="number" name="page" id="ir_pagina" min="1" max="{{ $ultima }}"
                           class="form-control form-control-sm" placeholder="{{ $atual }}" required>
                    <button type="submit" class="btn btn-sm btn-secondary">Ir</button>
                </form>
            </div>
        @endif
    </nav>
@endif
