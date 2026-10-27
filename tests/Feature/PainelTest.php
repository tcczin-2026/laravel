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

        $this->assertSame(10, substr_count($pagina1, '>Mega Drive</a>'));
        $this->assertSame(5, substr_count($pagina2, '>Mega Drive</a>'));
    }

    public function test_historico_do_painel_busca_pelo_id(): void
    {
        $aux = $this->auxiliares();
        $dados = [
            'quantidade' => 1,
            'plataformaConsole' => $aux['plataforma'], 'estado' => $aux['usado'], 'leitor' => $aux['digital'],
            'cores' => $aux['cor'], 'vintage' => $aux['retro'], 'aberto' => $aux['desbloqueado'],
            'colecionador' => $aux['edicao_especial'],
        ];
        $mega  = Console::create(['nome' => 'Mega Drive'] + $dados);
        $snes  = Console::create(['nome' => 'Super Nintendo'] + $dados);
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->get("/colecao?id={$snes->id}")
            ->assertOk()->assertSee('Super Nintendo')->assertSee("Console #{$snes->id}")->assertDontSee('>Mega Drive</a>', false);

        $this->actingAs($usuario)->get("/colecao?id={$mega->id}&tipo=console")
            ->assertSee('Mega Drive')->assertDontSee('Super Nintendo');

        $this->actingAs($usuario)->get("/colecao?id={$mega->id}&tipo=jogo")
            ->assertSee('Nenhuma alteração encontrada para essa busca.');
    }

    public function test_historico_do_painel_mostra_alteracao_usuario_e_link(): void
    {
        $aux = $this->auxiliares();
        $console = Console::create([
            'nome' => 'Mega Drive', 'quantidade' => 15,
            'plataformaConsole' => $aux['plataforma'], 'estado' => $aux['usado'], 'leitor' => $aux['digital'],
            'cores' => $aux['cor'], 'vintage' => $aux['retro'], 'aberto' => $aux['desbloqueado'],
            'colecionador' => $aux['edicao_especial'],
        ]);
        $ana = \App\Models\User::factory()->create(['name' => 'Ana']);
        $this->actingAs($ana);
        $console->update(['quantidade' => 17]);

        $this->get('/colecao')
            ->assertSeeInOrder(['Mega Drive', 'Atualizado', 'quantidade:', '15', '17', 'Ana'])
            ->assertSee(route('console.index', ['id' => $console->id]), false)
            ->assertDontSee('list-group-item'); // não usa mais a lista branca do Bootstrap
    }

    public function test_contagem_ignora_itens_com_quantidade_zero(): void
    {
        $aux = $this->auxiliares();
        foreach (['Com estoque' => 3, 'Pouco' => 1, 'Esgotado' => 0, 'Outro esgotado' => 0] as $nome => $qtd) {
            DB::table('jogo')->insert([
                'nome' => $nome, 'quantidade' => $qtd, 'Plataforma' => $aux['plataforma'],
                'estado' => $aux['usado'], 'vintage' => $aux['retro'], 'colecionador' => $aux['edicao_especial'],
            ]);
        }

        $html = $this->actingAs($this->funcionario())->get('/colecao')->getContent();

        // card "jogo" mostra 2 (os 2 esgotados não contam)
        $this->assertMatchesRegularExpression('#jogo</h5>\s*<p class="display-6 mb-0">2</p>#', $html);
    }

    public function test_historico_mostra_nomes_e_nao_ids(): void
    {
        $aux   = $this->auxiliares();
        $azul  = DB::table('cor')->insertGetId(['nome' => 'azul']);
        $verde = DB::table('cor')->insertGetId(['nome' => 'verde']);
        $console = Console::create([
            'nome' => 'Mega Drive', 'quantidade' => 1,
            'plataformaConsole' => $aux['plataforma'], 'estado' => $aux['usado'], 'leitor' => $aux['digital'],
            'cores' => $azul, 'vintage' => $aux['retro'], 'aberto' => $aux['desbloqueado'],
            'colecionador' => $aux['edicao_especial'],
        ]);
        $usuario = $this->funcionario();
        $this->actingAs($usuario);
        $console->update(['cores' => $verde]);

        // painel
        $this->get('/colecao')->assertSeeInOrder(['cor:', 'azul', 'verde'])->assertDontSee('cores:');

        // modal da listagem e tela de detalhes
        $this->get('/console')->assertSeeInOrder(['tabela-historico', 'cor:', 'azul', 'verde'], false);
        $this->get("/console/{$console->id}")->assertSeeInOrder(['tabela-historico', 'cor:', 'azul', 'verde'], false);

        // a reversão continua usando o id guardado no banco
        $linha = ConsoleHistorico::where('campo', 'cores')->first();
        $this->assertSame((string) $azul, (string) $linha->valor_anterior);
        $this->post(route('historico.desfazer', ['console', $linha->id]));
        $this->assertSame($azul, (int) $console->refresh()->cores);
    }

    public function test_valor_auxiliar_excluido_aparece_como_id(): void
    {
        $linha = new ConsoleHistorico(['campo' => 'cores', 'valor_anterior' => 999, 'valor_novo' => null]);

        $this->assertSame('cor', $linha->campo_rotulo);
        $this->assertSame('#999 (excluído)', $linha->de_texto);
        $this->assertNull($linha->para_texto);
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
