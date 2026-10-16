<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication {
        createApplication as protected criarAplicacao;
    }

    /**
     * Trava de segurança: roda ANTES do RefreshDatabase apagar o banco.
     * Se o teste não estiver no SQLite em memória (ex: alguém rodou
     * "php artisan config:cache"), para tudo antes de tocar no banco real.
     */
    public function createApplication()
    {
        $app = $this->criarAplicacao();

        $conexao = $app['config']->get('database.default');
        $banco   = $app['config']->get("database.connections.{$conexao}.database");

        if ($conexao !== 'sqlite' || $banco !== ':memory:') {
            throw new \RuntimeException(
                "Testes abortados: estariam usando o banco [{$conexao}: {$banco}] e iriam apagá-lo. "
                . 'Rode "php artisan config:clear" e confira o phpunit.xml.'
            );
        }

        return $app;
    }

    protected function gerente(): User
    {
        return User::factory()->create(['perfil' => 'gerente']);
    }

    protected function funcionario(): User
    {
        return User::factory()->create(['perfil' => 'funcionario']);
    }

    /**
     * Cria 1 registro em cada tabela auxiliar e devolve os ids,
     * pra usar nos campos de chave estrangeira dos cadastros.
     */
    protected function auxiliares(): array
    {
        $ids = [];
        foreach (['plataforma', 'cor', 'retro', 'usado', 'digital', 'desbloqueado', 'edicao_especial'] as $tabela) {
            $ids[$tabela] = DB::table($tabela)->insertGetId(['nome' => ucfirst($tabela) . ' teste']);
        }

        return $ids;
    }
}
