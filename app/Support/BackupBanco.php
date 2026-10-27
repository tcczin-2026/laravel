<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Backup e restauração do banco MySQL usando o mysqldump / mysql do XAMPP.
 * Os arquivos ficam em storage/app/backup/ (fora do git).
 *
 * O caminho dos programas pode ser trocado no .env:
 *     MYSQL_BIN=C:/xxampp/mysql/bin
 */
class BackupBanco
{
    public static function pasta(): string
    {
        return storage_path('app/backup');
    }

    /** Faz o backup completo (estrutura + dados) e devolve o caminho do arquivo */
    public static function fazer(string $motivo = 'manual'): string
    {
        File::ensureDirectoryExists(self::pasta());

        $motivo  = preg_replace('/[^a-z0-9]+/i', '-', $motivo);
        $arquivo = self::pasta() . '/' . date('Y-m-d_His') . "_{$motivo}.sql";

        $resultado = Process::timeout(120)->run(array_merge(
            [self::programa('mysqldump')],
            self::credenciais(),
            ['--single-transaction', '--routines', '--result-file=' . $arquivo, config('database.connections.mysql.database')]
        ));

        if (! $resultado->successful() || ! is_file($arquivo) || filesize($arquivo) === 0) {
            @unlink($arquivo);
            throw new RuntimeException('Não foi possível fazer o backup do banco: '
                . trim($resultado->errorOutput() ?: 'mysqldump não gerou o arquivo') . '. O MySQL está ligado?');
        }

        return $arquivo;
    }

    /** Apaga o banco atual e coloca no lugar o conteúdo do arquivo de backup */
    public static function restaurar(string $arquivo): void
    {
        if (! is_file($arquivo)) {
            throw new RuntimeException("Arquivo de backup não encontrado: {$arquivo}");
        }

        $resultado = Process::timeout(300)
            ->input(file_get_contents($arquivo))
            ->run(array_merge([self::programa('mysql')], self::credenciais(), [config('database.connections.mysql.database')]));

        if (! $resultado->successful()) {
            throw new RuntimeException('Falha ao restaurar: ' . trim($resultado->errorOutput()));
        }
    }

    /** Backups existentes, do mais novo pro mais antigo */
    public static function lista(): array
    {
        // só os backups completos ("2026-10-27_153000_motivo.sql"); arquivos avulsos ficam de fora
        $arquivos = glob(self::pasta() . '/[0-9][0-9][0-9][0-9]-*.sql') ?: [];
        rsort($arquivos);

        return $arquivos;
    }

    private static function credenciais(): array
    {
        $c = config('database.connections.mysql');
        $args = ['--host=' . $c['host'], '--port=' . $c['port'], '--user=' . $c['username']];

        if ((string) $c['password'] !== '') {
            $args[] = '--password=' . $c['password'];
        }

        return $args;
    }

    private static function programa(string $nome): string
    {
        $pasta = rtrim((string) env('MYSQL_BIN', 'C:/xxampp/mysql/bin'), '/\\');
        $exe   = $pasta . '/' . $nome . (PHP_OS_FAMILY === 'Windows' ? '.exe' : '');

        return is_file($exe) ? $exe : $nome; // senão, procura no PATH
    }
}
