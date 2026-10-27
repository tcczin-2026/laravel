<?php

namespace App\Console\Commands;

use App\Support\BackupBanco;
use Illuminate\Console\Command;

class BancoRestaurar extends Command
{
    protected $signature = 'banco:restaurar {arquivo? : caminho do .sql (sem isso, mostra a lista para escolher)}';

    protected $description = 'Volta o banco para um backup de storage/app/backup';

    public function handle(): int
    {
        $arquivo = $this->argument('arquivo') ?? $this->escolher();

        if (! $arquivo) {
            return self::FAILURE;
        }

        $this->warn('O banco atual será SUBSTITUÍDO pelo conteúdo de: ' . basename($arquivo));

        if (! $this->confirm('Continuar? (um backup do estado atual é feito antes)')) {
            $this->line('Nada foi alterado.');

            return self::SUCCESS;
        }

        try {
            $seguranca = BackupBanco::fazer('antes-de-restaurar');
            BackupBanco::restaurar($arquivo);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Banco restaurado.');
        $this->line('O estado anterior ficou salvo em: ' . basename($seguranca));

        return self::SUCCESS;
    }

    private function escolher(): ?string
    {
        $backups = BackupBanco::lista();

        if (! $backups) {
            $this->error('Nenhum backup encontrado em ' . BackupBanco::pasta());

            return null;
        }

        $nomes = array_map(fn ($a) => sprintf('%s  (%d KB)', basename($a), filesize($a) / 1024), $backups);
        $escolha = $this->choice('Qual backup restaurar? (o primeiro é o mais recente)', $nomes, 0);

        return $backups[array_search($escolha, $nomes, true)];
    }
}
