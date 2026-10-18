<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /** Opções do seletor "Itens por página" (a primeira é o padrão) */
    public const POR_PAGINA = [10, 25, 50, 100];

    /** Regra de validação do preço, igual nas 4 tabelas principais */
    protected const REGRA_VALOR = 'nullable|numeric|min:0|max:99999999.99';

    /**
     * Aceita o preço no formato brasileiro: "1.299,90" -> "1299.90".
     * (O campo do formulário já manda "1299.90"; isso cobre quem digita com vírgula.)
     */
    protected function normalizarValor(\Illuminate\Http\Request $request): void
    {
        $valor = trim((string) $request->input('valor'));

        if (str_contains($valor, ',')) {
            $request->merge(['valor' => str_replace(['.', ','], ['', '.'], $valor)]);
        }
    }

    /** Quantos itens por página o usuário escolheu (?por_pagina=25); valor fora da lista volta ao padrão */
    protected function porPagina(): int
    {
        $escolhido = (int) request('por_pagina');

        return in_array($escolhido, self::POR_PAGINA, true) ? $escolhido : self::POR_PAGINA[0];
    }
}
