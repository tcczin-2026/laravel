{{-- Select reutilizavel para as chaves estrangeiras --}}
@php $valor = old($campo, $selecionado ?? null); @endphp

<div class="col-md-4 mb-3">
    <label for="{{ $campo }}" class="form-label">{{ $rotulo }}</label>
    <select name="{{ $campo }}" id="{{ $campo }}" class="form-select" required>
        <option value=""> selecione </option>
        @foreach ($opcoes as $opcao)
            <option value="{{ $opcao->id }}" @selected($valor == $opcao->id)>{{ $opcao->nome }}</option>
        @endforeach
    </select>
    @if ($opcoes->isEmpty())
        <div class="form-text text-danger">Nenhuma opcao cadastrada nesta tabela auxiliar.</div>
    @endif
</div>

