{{-- Etiqueta de estoque: verde (> 10), amarela (1 a 10) ou vermelha (0) --}}
@if ($quantidade > 10)
    <span class="stock-pill stock-pill--ok">{{ $quantidade }} · Em estoque</span>
@elseif ($quantidade > 0)
    <span class="stock-pill stock-pill--low">{{ $quantidade }} · Pouco restante</span>
@else
    <span class="stock-pill stock-pill--out">Esgotado</span>
@endif
