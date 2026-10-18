<?php

namespace App\Models\Concerns;

/**
 * Campo "valor" (preço) das tabelas principais.
 * Na view: {{ $jogo->valor_formatado }}  ->  "R$ 1.299,90" (ou "-" se vazio)
 */
trait TemValor
{
    public function initializeTemValor(): void
    {
        $this->mergeCasts(['valor' => 'decimal:2']);
    }

    public function getValorFormatadoAttribute(): string
    {
        return $this->valor === null ? '-' : 'R$ ' . number_format((float) $this->valor, 2, ',', '.');
    }
}
