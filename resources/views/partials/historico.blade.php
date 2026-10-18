{{--
    Card "Histórico de alterações" das páginas de detalhes (show).
    A tabela em si fica em partials/historico-tabela (a mesma do modal "Ver").

    Como usar:
        @include('partials.historico', ['historico' => $console->historico, 'tipo' => 'console'])
    (sem 'tipo', não mostra os botões de reverter)
--}}

<div class="card card-body mb-4">
    <h5 class="mb-3">Histórico de alterações</h5>

    @include('partials.historico-tabela', ['historico' => $historico, 'tipo' => $tipo ?? null])
</div>
