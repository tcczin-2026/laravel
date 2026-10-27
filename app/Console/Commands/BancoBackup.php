<?php

namespace App\Console\Commands;

use App\Support\BackupBanco;
use Illuminate\Console\Command;

class BancoBackup extends Command
{
    protected $signature = 'banco:backup';

    protected $description = 'Faz um backup completo do banco em storage/app/backup';

    public function handle(): int
    {
        try {
            $arquivo = BackupBanco::fazer('manual');
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Backup salvo em: ' . $arquivo);
        $this->line('Para voltar a ele: php artisan banco:restaurar');

        return self::SUCCESS;
    }
}
