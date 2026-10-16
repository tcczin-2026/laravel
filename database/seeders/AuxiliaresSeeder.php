<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Preenche as 7 tabelas auxiliares com os valores usados no sistema
 * (copiados do banco em 16/10/2026).
 *
 * Os ids são fixos de propósito: console, controle, jogo e acessorio
 * guardam o id (ex: cores = 7 é "preto"), então mudar o id mudaria o
 * significado dos cadastros.
 *
 * Pode rodar quantas vezes quiser: se o id já existe, só atualiza o nome.
 *     php artisan db:seed --class=AuxiliaresSeeder
 */
class AuxiliaresSeeder extends Seeder
{
    private const DADOS = [
        'plataforma' => [
            1 => 'playstation',
            2 => 'xbox',
            3 => 'nintendo',
        ],
        'cor' => [
            1  => 'azul',
            2  => 'amarelo',
            3  => 'branco',
            4  => 'cinza',
            5  => 'laranja',
            6  => 'marrom',
            7  => 'preto',
            8  => 'rosa',
            9  => 'roxo',
            10 => 'verde',
            11 => 'vermelho',
        ],
        'retro' => [
            1 => 'retro',
            2 => 'atual',
        ],
        'usado' => [
            1 => 'novo',
            2 => 'semi-novo',
        ],
        'digital' => [
            1 => 'digital',
            2 => 'fisico',
        ],
        'desbloqueado' => [
            1 => 'bloqueado',
            2 => 'desbloqueado',
        ],
        'edicao_especial' => [
            1 => 'padrao',
            2 => 'especial',
        ],
    ];

    public function run(): void
    {
        foreach (self::DADOS as $tabela => $registros) {
            $linhas = [];
            foreach ($registros as $id => $nome) {
                $linhas[] = ['id' => $id, 'nome' => $nome];
            }

            // Insere o que falta; o que já existe (mesmo id) tem o nome atualizado
            DB::table($tabela)->upsert($linhas, ['id'], ['nome']);
        }
    }
}
