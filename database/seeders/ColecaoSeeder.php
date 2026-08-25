<?php

namespace Database\Seeders;

use App\Models\Cor;
use App\Models\Desbloqueado;
use App\Models\Digital;
use App\Models\EdicaoEspecial;
use App\Models\Marca;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Database\Seeder;

/**
 * Popula as tabelas auxiliares com valores iniciais para que os
 * formularios de console, controle, jogo e acessorio ja tenham opcoes.
 */
class ColecaoSeeder extends Seeder
{
    public function run(): void
    {
        $conteudo = [
            Marca::class          => ['Nintendo', 'Sony', 'Microsoft', 'Sega', 'Atari'],
            Cor::class            => ['Preto', 'Branco', 'Cinza', 'Vermelho', 'Azul', 'Transparente'],
            Retro::class          => ['Sim', 'Nao'],
            Usado::class          => ['Novo', 'Usado', 'Semi-novo'],
            Digital::class        => ['Fisico', 'Digital', 'Hibrido'],
            Desbloqueado::class   => ['Sim', 'Nao'],
            EdicaoEspecial::class => ['Sim', 'Nao'],
        ];

        foreach ($conteudo as $model => $nomes) {
            foreach ($nomes as $nome) {
                $model::firstOrCreate(['nome' => $nome]);
            }
        }
    }
}
