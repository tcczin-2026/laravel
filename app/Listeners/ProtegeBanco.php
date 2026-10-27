<?php

namespace App\Listeners;

use App\Support\BackupBanco;
use Illuminate\Console\Events\CommandStarting;
use RuntimeException;

/**
 * Protege o banco MySQL contra comandos que apagam dados.
 *
 *   php artisan migrate:fresh          -> BLOQUEADO, com explicação
 *   php artisan migrate:fresh --force  -> faz backup em storage/app/backup e depois roda
 *
 * Não atua nos testes (SQLite em memória).
 */
class ProtegeBanco
{
    private const DESTRUTIVOS = [
        'migrate:fresh'    => 'APAGA todas as tabelas e recria vazias',
        'migrate:refresh'  => 'desfaz e refaz todas as migrations (apaga os dados)',
        'migrate:reset'    => 'desfaz todas as migrations (apaga as tabelas)',
        'migrate:rollback' => 'desfaz as últimas migrations (pode apagar colunas e dados)',
        'db:wipe'          => 'APAGA todas as tabelas',
    ];

    public function handle(CommandStarting $evento): void
    {
        $comando = $evento->command;

        if (! isset(self::DESTRUTIVOS[$comando]) || ! $this->bancoReal()) {
            return;
        }

        $banco = config('database.connections.mysql.database');
        $saida = $evento->output;

        if (! $evento->input->hasParameterOption(['--force', '-f'], true)) {
            $saida->writeln('');
            $saida->writeln("<error> ATENÇÃO: \"{$comando}\" " . self::DESTRUTIVOS[$comando] . " no banco \"{$banco}\". </error>");
            $saida->writeln('  Isso inclui itens, histórico e usuários.');
            $saida->writeln('');
            $saida->writeln('  - Para só preencher os dados (sem apagar nada):  <info>php artisan db:seed</info>');
            $saida->writeln("  - Se tem certeza que quer apagar:                <info>php artisan {$comando} --force</info>");
            $saida->writeln('    (um backup é feito antes, e dá pra voltar com: php artisan banco:restaurar)');
            $saida->writeln('');

            throw new RuntimeException("Comando \"{$comando}\" bloqueado para proteger o banco. Nada foi alterado.");
        }

        $saida->writeln('<comment>Fazendo backup do banco antes de continuar...</comment>');
        $arquivo = BackupBanco::fazer("antes-{$comando}");
        $saida->writeln('<info>Backup salvo em: ' . $arquivo . '</info>');
        $saida->writeln('<info>Para voltar: php artisan banco:restaurar</info>');
    }

    /** Só protege o MySQL de verdade (nos testes o banco é SQLite em memória) */
    protected function bancoReal(): bool
    {
        return config('database.default') === 'mysql' && ! app()->runningUnitTests();
    }
}
