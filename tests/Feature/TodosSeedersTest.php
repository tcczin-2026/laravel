<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Roda CADA seeder da pasta database/seeders num banco vazio.
 * Se alguém criar ou mudar um seeder e ele quebrar (como aconteceu com o
 * ColecaoSeeder usando um model que não existia mais), este teste avisa.
 */
class TodosSeedersTest extends TestCase
{
    use RefreshDatabase;

    public static function seeders(): array
    {
        $lista = [];
        // (o Laravel ainda não iniciou aqui, então o caminho é relativo a este arquivo)
        foreach (glob(__DIR__ . '/../../database/seeders/*.php') as $arquivo) {
            $classe = 'Database\\Seeders\\' . basename($arquivo, '.php');
            $lista[basename($arquivo, '.php')] = [$classe];
        }

        return $lista;
    }

    #[DataProvider('seeders')]
    public function test_seeder_roda_sem_erro(string $classe): void
    {
        Storage::fake('local'); // o DemonstracaoSeeder grava um arquivo de controle

        $this->assertTrue(class_exists($classe), "A classe {$classe} não bate com o nome do arquivo.");

        $this->seed($classe);
        $this->seed($classe); // e roda de novo sem quebrar

        $this->assertTrue(true);
    }

    public function test_colecao_seeder_monta_auxiliares_e_acervo(): void
    {
        $this->seed(\Database\Seeders\ColecaoSeeder::class);

        $this->assertSame(4, DB::table('plataforma')->count());
        $this->assertSame(11, DB::table('console')->count());
    }

    public function test_historico_de_exemplo_usa_campos_que_existem(): void
    {
        $this->seed(\Database\Seeders\ColecaoSeeder::class);
        $this->seed(\Database\Seeders\HistoricoTesteSeeder::class);

        $this->assertDatabaseMissing('jogo_historico', ['campo' => 'cores']);
        $this->assertTrue(DB::table('jogo_historico')->where('campo', 'quantidade')->exists());
    }
}
