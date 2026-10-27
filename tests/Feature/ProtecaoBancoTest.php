<?php

namespace Tests\Feature;

use App\Listeners\ProtegeBanco;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

/** Proteção contra apagar o banco e página de "banco fora do ar" */
class ProtecaoBancoTest extends TestCase
{
    use RefreshDatabase;

    /** Listener como se estivesse no MySQL de verdade (nos testes ele fica desligado) */
    private function protecaoNoBancoReal(): ProtegeBanco
    {
        return new class extends ProtegeBanco {
            protected function bancoReal(): bool
            {
                return true;
            }
        };
    }

    private function evento(string $linha, BufferedOutput $saida): CommandStarting
    {
        $argv  = array_merge(['artisan'], explode(' ', $linha));
        $input = new ArgvInput($argv);

        return new CommandStarting($argv[1], $input, $saida);
    }

    public static function destrutivos(): array
    {
        return [['migrate:fresh'], ['migrate:fresh --seed'], ['migrate:refresh'], ['migrate:reset'], ['migrate:rollback'], ['db:wipe']];
    }

    #[DataProvider('destrutivos')]
    public function test_comando_destrutivo_sem_force_e_bloqueado(string $linha): void
    {
        $saida = new BufferedOutput();

        try {
            $this->protecaoNoBancoReal()->handle($this->evento($linha, $saida));
            $this->fail("\"{$linha}\" deveria ter sido bloqueado");
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('bloqueado', $e->getMessage());
        }

        $this->assertStringContainsString('php artisan db:seed', $saida->fetch());
    }

    public function test_comandos_comuns_passam_direto(): void
    {
        foreach (['migrate', 'db:seed', 'migrate:status', 'serve', 'banco:backup'] as $linha) {
            $this->protecaoNoBancoReal()->handle($this->evento($linha, new BufferedOutput()));
        }

        $this->assertTrue(true);
    }

    public function test_nos_testes_a_protecao_nao_atrapalha(): void
    {
        // o RefreshDatabase roda migrate:fresh no SQLite em memória a cada teste
        (new ProtegeBanco())->handle($this->evento('migrate:fresh', new BufferedOutput()));

        $this->assertTrue(true);
    }

    public function test_banco_fora_do_ar_mostra_pagina_amigavel(): void
    {
        Route::get('/_teste_banco_fora', function () {
            throw new QueryException('mysql', 'select 1', [],
                new \PDOException('SQLSTATE[HY000] [2002] Nenhuma conexão pôde ser feita'));
        });

        $this->get('/_teste_banco_fora')
            ->assertStatus(503)
            ->assertSee('Banco de dados fora do ar')
            ->assertSee('Start')
            ->assertDontSee('QueryException');
    }

    public function test_outros_erros_de_banco_nao_viram_a_pagina_de_fora_do_ar(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(QueryException::class);

        Route::get('/_teste_sql_errado', function () {
            throw new QueryException('mysql', 'select x', [], new \PDOException("SQLSTATE[42S22]: Column not found"));
        });

        $this->get('/_teste_sql_errado');
    }
}
