<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Apaga os dados criados pelo DemonstracaoSeeder (e o histórico deles),
 * usando os ids anotados em storage/app/demonstracao.json.
 * Os dados reais não são tocados.
 *
 *     php artisan db:seed --class=RemoverDemonstracaoSeeder
 */
class RemoverDemonstracaoSeeder extends Seeder
{
    public function run(): void
    {
        if (! Storage::exists(DemonstracaoSeeder::ARQUIVO)) {
            $this->command?->warn('Não há demonstração para remover.');

            return;
        }

        $ids = json_decode(Storage::get(DemonstracaoSeeder::ARQUIVO), true);

        DB::transaction(function () use ($ids) {
            // Controles primeiro (apontam para consoles). DB::table não dispara o
            // Observer, então não fica linha de "excluido" no histórico.
            foreach (['controle', 'acessorio', 'jogo', 'console'] as $tabela) {
                DB::table("{$tabela}_historico")->whereIn("{$tabela}_id", $ids[$tabela])->delete();
                DB::table($tabela)->whereIn('id', $ids[$tabela])->delete();
            }

            DB::table('users')->whereIn('id', $ids['users'])->delete();
        });

        Storage::delete(DemonstracaoSeeder::ARQUIVO);

        $this->command?->info('Demonstração removida. Os dados reais continuam como estavam.');
    }
}
