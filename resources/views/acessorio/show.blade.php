
<div class="card card-body mb-4">
@include('partials.historico', ['historico' => $acessorio->historico]) 
    <h5 class="mb-3">Histórico de alterações</h5>

    @if ($historico->isEmpty())
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
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td>
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