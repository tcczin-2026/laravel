<?php

namespace Tests\Feature;

use Database\Seeders\AcervoSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcervoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_preenche_o_acervo_com_valor_armazenamento_e_foto(): void
    {
        $this->seed(AcervoSeeder::class);

        $this->assertSame(11, DB::table('console')->count());
        $this->assertSame(1, DB::table('controle')->count());
        $this->assertSame(1, DB::table('jogo')->count());
        $this->assertSame(1, DB::table('acessorio')->count());

        $ps5 = DB::table('console')->find(1);
        $this->assertSame('playstation 5', $ps5->nome);
        $this->assertEquals(5200, (float) $ps5->valor);
        $this->assertSame(825, (int) $ps5->armazenamento);
        $this->assertStringStartsWith('consoles/', $ps5->imagem);

        // o controle usa a cor nova "azul e vermelho" (id 12)
        $this->assertSame(12, (int) DB::table('controle')->value('cores'));
        $this->assertDatabaseHas('jogo', ['id' => 1, 'nome' => "Marvel's spider-man 2"]);
    }

    public function test_rodar_duas_vezes_nao_duplica_e_corrige_alteracoes(): void
    {
        $this->seed(AcervoSeeder::class);
        DB::table('console')->where('id', 1)->update(['quantidade' => 999]);

        $this->seed(AcervoSeeder::class);

        $this->assertSame(11, DB::table('console')->count());
        $this->assertSame(5, (int) DB::table('console')->where('id', 1)->value('quantidade'));
    }

    public function test_nao_gera_historico(): void
    {
        $this->seed(AcervoSeeder::class);

        $this->assertSame(0, DB::table('console_historico')->count());
    }

    public function test_database_seeder_monta_o_banco_completo_e_as_telas_abrem(): void
    {
        $this->seed(DatabaseSeeder::class);
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->get('/console')->assertOk()->assertSee('R$ 5.200,00')->assertSee('825 GB');
        $this->actingAs($usuario)->get('/controle')->assertOk()->assertSee('azul e vermelho');
        $this->actingAs($usuario)->get('/colecao')->assertOk();
    }

    public function test_cadastrar_novo_item_depois_do_seed_nao_colide_com_os_ids(): void
    {
        $this->seed(AcervoSeeder::class);

        $id = DB::table('console')->insertGetId([
            'nome' => 'Novo', 'quantidade' => 1, 'plataformaConsole' => 4, 'estado' => 1, 'leitor' => 1,
            'cores' => 1, 'vintage' => 1, 'aberto' => 1, 'colecionador' => 1,
        ]);

        $this->assertGreaterThan(12, $id);
    }
}
