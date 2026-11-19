<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Modo offline: rotas de apoio e o reenvio das alterações guardadas no aparelho
 * (RespostaSincronizacao + OfflineController).
 */
class OfflineTest extends TestCase
{
    use RefreshDatabase;

    private const SYNC = ['X-Offline-Sync' => '1', 'Accept' => 'application/json'];

    private function dadosConsole(array $a, array $extra = []): array
    {
        return array_merge([
            'nome' => 'Mega Drive', 'quantidade' => 2, 'valor' => '499.90', 'armazenamento' => 1,
            'plataformaConsole' => $a['plataforma'], 'estado' => $a['usado'], 'leitor' => $a['digital'],
            'cores' => $a['cor'], 'vintage' => $a['retro'], 'aberto' => $a['desbloqueado'],
            'colecionador' => $a['edicao_especial'],
        ], $extra);
    }

    public function test_ping_responde_sem_login_e_sem_cache(): void
    {
        $this->get('/offline/ping')->assertNoContent()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_token_e_paginas_exigem_login(): void
    {
        $this->getJson('/offline/token')->assertUnauthorized();
        $this->getJson('/offline/paginas')->assertUnauthorized();
    }

    public function test_token_devolve_csrf_e_usuario(): void
    {
        $u = $this->gerente();
        $this->actingAs($u)->getJson('/offline/token')
            ->assertOk()->assertJsonStructure(['token'])->assertJson(['usuario' => $u->id]);
    }

    public function test_lista_de_paginas_inclui_itens_e_paginacao(): void
    {
        $a = $this->auxiliares();
        for ($i = 1; $i <= 11; $i++) {
            DB::table('console')->insert($this->dadosConsole($a, ['nome' => "C{$i}", 'valor' => 10]));
        }
        $id = DB::table('console')->min('id');

        $urls = $this->actingAs($this->gerente())->getJson('/offline/paginas')->assertOk()->json('urls');

        $this->assertContains(route('console.index'), $urls);
        $this->assertContains(route('console.index') . '?page=2', $urls);
        $this->assertContains(route('console.edit', $id), $urls);
        $this->assertContains(route('console.show', $id), $urls);
        $this->assertContains(route('usuario.index'), $urls);
        $this->assertContains(route('auxiliar.edit', ['cor', $a['cor']]), $urls);
    }

    public function test_funcionario_nao_recebe_paginas_de_gerente(): void
    {
        $urls = $this->actingAs($this->funcionario())
            ->getJson('/offline/paginas')->json('urls');

        $this->assertNotContains(route('usuario.index'), $urls);
    }

    public function test_cadastro_feito_offline_e_gravado_no_banco(): void
    {
        $a = $this->auxiliares();

        $this->actingAs($this->gerente())
            ->post('/console', $this->dadosConsole($a), self::SYNC + ['X-Offline-Id' => 'abc12345-1'])
            ->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('console', ['nome' => 'Mega Drive']);
        // a mensagem de sucesso não fica "sobrando" para a próxima página
        $this->get('/console')->assertDontSee('Console cadastrado com sucesso');
    }

    public function test_mesma_alteracao_enviada_duas_vezes_grava_uma_vez(): void
    {
        $a = $this->auxiliares();
        $this->actingAs($this->gerente());
        $cabecalhos = self::SYNC + ['X-Offline-Id' => 'repetida-0001'];

        $this->post('/console', $this->dadosConsole($a), $cabecalhos)->assertOk();
        $this->post('/console', $this->dadosConsole($a), $cabecalhos)->assertOk()->assertJson(['repetido' => true]);

        $this->assertSame(1, DB::table('console')->count());
    }

    public function test_edicao_e_exclusao_offline(): void
    {
        $a = $this->auxiliares();
        $id = DB::table('console')->insertGetId($this->dadosConsole($a));
        $this->actingAs($this->gerente());

        $this->post("/console/{$id}", ['_method' => 'PUT'] + $this->dadosConsole($a, ['nome' => 'Saturn']), self::SYNC)
            ->assertOk();
        $this->assertDatabaseHas('console', ['id' => $id, 'nome' => 'Saturn']);

        $this->post("/console/{$id}", ['_method' => 'DELETE'], self::SYNC)->assertOk();
        $this->assertDatabaseMissing('console', ['id' => $id]);
    }

    public function test_validacao_volta_422_com_os_erros(): void
    {
        $this->actingAs($this->gerente())
            ->post('/console', ['nome' => ''], self::SYNC)
            ->assertStatus(422)->assertJsonValidationErrors('nome');
    }

    public function test_recusa_do_controller_volta_409_com_a_mensagem(): void
    {
        $u = $this->gerente();

        $this->actingAs($u)->post("/usuario/{$u->id}", ['_method' => 'DELETE'], self::SYNC)
            ->assertStatus(409)->assertJson(['ok' => false, 'mensagem' => 'Você não pode excluir o seu próprio usuário.']);
    }

    public function test_sessao_expirada_volta_401(): void
    {
        $this->post('/console', [], self::SYNC)->assertUnauthorized();
    }

    public function test_item_excluido_no_servidor_volta_404(): void
    {
        $this->actingAs($this->gerente())
            ->post('/console/999', ['_method' => 'DELETE'], self::SYNC)
            ->assertNotFound();
    }

    public function test_sem_o_cabecalho_continua_redirecionando(): void
    {
        $a = $this->auxiliares();
        $this->actingAs($this->gerente())->post('/console', $this->dadosConsole($a))->assertRedirect(route('console.index'));
    }

    public function test_layout_carrega_o_modo_offline(): void
    {
        $this->actingAs($this->gerente())->get('/colecao')->assertOk()
            ->assertSee('js/offline.js', false)
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('id="aviso-conexao"', false)
            ->assertDontSee('serviceworker.js', false); // o antigo brigava com o public/sw.js
    }
}
