<?php

namespace Database\Seeders;

use App\Models\Console;
use App\Models\ConsoleHistorico;
use App\Models\Controle;
use App\Models\ControleHistorico;
use Illuminate\Database\Seeder;

class HistoricoTesteSeeder extends Seeder
{
    /**
     * Popula histórico de exemplo pro PRIMEIRO console e o PRIMEIRO
     * controle já cadastrados no banco. Serve só pra você conseguir
     * ver a tela funcionando antes de ter alterações reais no sistema
     * (e antes de ter login pra saber "quem" alterou).
     *
     * Rodar com: php artisan db:seed --class=HistoricoTesteSeeder
     */
    public function run(): void
    {
        $console = Console::first();
        $controle = Controle::first();

        if ($console) {
            // insert() grava direto no banco (não passa pelos Observers,
            // então não gera histórico duplicado) e aceita created_at
            // manual pra simular datas diferentes.
            ConsoleHistorico::insert([
                [
                    'console_id'     => $console->id,
                    'acao'           => 'criado',
                    'campo'          => null,
                    'valor_anterior' => null,
                    'valor_novo'     => "nome: {$console->nome}, quantidade: {$console->quantidade}",
                    'usuario'        => 'Sistema',
                    'created_at'     => now()->subDays(5)->toDateTimeString(),
                ],
                [
                    'console_id'     => $console->id,
                    'acao'           => 'atualizado',
                    'campo'          => 'quantidade',
                    'valor_anterior' => '1',
                    'valor_novo'     => '3',
                    'usuario'        => 'Sistema',
                    'created_at'     => now()->subDays(2)->toDateTimeString(),
                ],
                [
                    'console_id'     => $console->id,
                    'acao'           => 'atualizado',
                    'campo'          => 'estado',
                    'valor_anterior' => '2',
                    'valor_novo'     => '1',
                    'usuario'        => 'Sistema',
                    'created_at'     => now()->subDay()->toDateTimeString(),
                ],
            ]);
        }

        if ($controle) {
            ControleHistorico::insert([
                [
                    'controle_id'    => $controle->id,
                    'acao'           => 'criado',
                    'campo'          => null,
                    'valor_anterior' => null,
                    'valor_novo'     => "nome: {$controle->nome}, quantidade: {$controle->quantidade}",
                    'usuario'        => 'Sistema',
                    'created_at'     => now()->subDays(4)->toDateTimeString(),
                ],
                [
                    'controle_id'    => $controle->id,
                    'acao'           => 'atualizado',
                    'campo'          => 'cores',
                    'valor_anterior' => '1',
                    'valor_novo'     => '4',
                    'usuario'        => 'Sistema',
                    'created_at'     => now()->subDay()->toDateTimeString(),
                ],
            ]);
        }
    }
}