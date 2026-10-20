<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Monta a coleção inteira: tabelas auxiliares (plataformas, cores...) e o
 * acervo (consoles, controles, jogos e acessórios com valor e foto).
 *
 * É o mesmo que o DatabaseSeeder roda; existe pra quem procurar pelo nome:
 *     php artisan db:seed --class=ColecaoSeeder
 */
class ColecaoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuxiliaresSeeder::class,
            AcervoSeeder::class,
        ]);
    }
}
