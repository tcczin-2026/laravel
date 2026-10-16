<?php

namespace Tests\Feature;

use App\Models\Console;
use App\Models\ConsoleHistorico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Painel inicial e tabelas auxiliares */
class PainelTest extends TestCase
{
    use RefreshDatabase;

    public function test_painel_mostra_totais(): void
    {
        $this->actingAs($this->funcionario())->get('/colecao')
            ->assertOk()->assertSee('Cadastros principais')->assertSee('Últimas alterações');
    }

    public function test_historico_do_painel_pagina_de_10_em_10(): void
    {
        $aux = $this->auxiliares();
        $console = Console::create([
            'nome' => 'Mega Drive', 'quantidade' => 0,
            'plataformaConsole' => $aux['plataforma'], 'estado' => $aux['usado'], 'leitor' => $aux['digital'],
            'cores' => $aux['cor'], 'vintage' => $aux['retro'], 'aberto' => $aux['desbloqueado'],
            'colecionador' => $aux['edicao_especial'],
        ]);
        for ($i = 1; $i <= 14; $i++) {
            $console->update(['quantidade' => $i]); // +1 linha de histórico cada
        }
        $this->assertSame(15, ConsoleHistorico::count());

        $usuario = $this->funcionario();
        $pagina1 = $this->actingAs($usuario)->get('/colecao')->assertOk()->assertSee('pagination')->getContent();
        $pagina2 = $this->actingAs($usuario)->get('/colecao?page=2')->assertOk()->getContent();

        $this->assertSame(10, substr_count($pagina1, '"Mega Drive"'));
        $this->assertSame(5, substr_count($pagina2, '"Mega Drive"'));
    }

    public static function tiposAuxiliares(): array
    {
        return [['plataforma'], ['cor'], ['retro'], ['usado'], ['digital'], ['desbloqueado'], ['edicao_especial']];
    }

    #[DataProvider('tiposAuxiliares')]
    public function test_gerente_faz_crud_de_auxiliar(string $tipo): void
    {
        $gerente = $this->gerente();

        $this->actingAs($gerente)->post("/auxiliar/{$tipo}", ['nome' => 'Novo'])
            ->assertRedirect(route('auxiliar.index', $tipo));
        $id = DB::table($tipo)->where('nome', 'Novo')->value('id');
        $this->assertNotNull($id);

        $this->actingAs($gerente)->get("/auxiliar/{$tipo}/{$id}/editar")->assertOk();
        $this->actingAs($gerente)->put("/auxiliar/{$tipo}/{$id}", ['nome' => 'Editado']);
        $this->assertDatabaseHas($tipo, ['id' => $id, 'nome' => 'Editado']);

        $this->actingAs($gerente)->delete("/auxiliar/{$tipo}/{$id}");
        $this->assertDatabaseMissing($tipo, ['id' => $id]);
    }

    public function test_auxiliar_em_uso_nao_pode_ser_excluido(): void
    {
        $aux = $this->auxiliares();
        DB::table('jogo')->insert([
            'nome' => 'Zelda', 'quantidade' => 1, 'Plataforma' => $aux['plataforma'],
            'estado' => $aux['usado'], 'vintage' => $aux['retro'], 'colecionador' => $aux['edicao_especial'],
        ]);

        $this->actingAs($this->gerente())->delete("/auxiliar/plataforma/{$aux['plataforma']}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('plataforma', ['id' => $aux['plataforma']]);
    }

    public function test_nome_auxiliar_maior_que_20_e_recusado(): void
    {
        $this->actingAs($this->gerente())->post('/auxiliar/cor', ['nome' => str_repeat('a', 21)])
            ->assertSessionHasErrors('nome');
    }

    public function test_tipo_auxiliar_inexistente_da_404(): void
    {
        $this->actingAs($this->gerente())->get('/auxiliar/inexistente')->assertNotFound();
    }
}
