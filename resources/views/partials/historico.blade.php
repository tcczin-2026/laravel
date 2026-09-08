{{--
    Bloco reutilizável de histórico de alterações.
    Funciona tanto pra console quanto pra controle, porque só espera
    uma coleção de registros com os campos: acao, campo, valor_anterior,
    valor_novo, usuario, created_at.

    Como usar:
        @include('partials.historico', ['historico' => $console->historico])
        @include('partials.historico', ['historico' => $controle->historico])
--}}

<div class="card card-body mb-4">
    <h5 class="mb-3">Histórico de alterações</h5>

    @if ($historico->isEmpty())
        {{-- Acontece pra qualquer registro criado antes do Observer existir,
             ou que ainda não sofreu nenhuma alteração --}}
        <p class="text-muted mb-0">Nenhuma alteração registrada ainda.</p>
    @else
        <div class="table-responsive">
            <table class="table table-dark table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Ação</th>
                        <th>Campo</th>
                        <th>De</th>
                        <th>Para</th>
                        <th>Usuário</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($historico as $item)
                        <tr>
                            {{-- created_at vem tipado como datetime (casts no model) --}}
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>

                            <td>
                                {{-- Badge colorido de acordo com o tipo de ação --}}
                                @switch($item->acao)
                                    @case('criado')
                                        <span class="badge bg-success">Criado</span>
                                        @break
                                    @case('atualizado')
                                        <span class="badge bg-warning text-dark">Atualizado</span>
                                        @break
                                    @case('excluido')
                                        <span class="badge bg-danger">Excluído</span>
                                        @break
                                @endswitch
                            </td>

                            {{-- Em 'criado'/'excluido' não há um campo específico,
                                 então mostramos "-" --}}
                            <td>{{ $item->campo ?? '-' }}</td>
                            <td>{{ $item->valor_anterior ?? '-' }}</td>
                            <td>{{ $item->valor_novo ?? '-' }}</td>
                            <td>{{ $item->usuario }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>