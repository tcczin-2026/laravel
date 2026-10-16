<?php

namespace Tests\Feature;

use App\Models\Console;
use App\Models\ConsoleHistorico;
use App\Models\Jogo;
use App\Models\JogoHistorico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Gravação do histórico e reversão (desfazer / restaurar até aqui) */
class HistoricoTest extends TestCase
{
    use RefreshDatabase;

    private Console $console;

    protected function setUp(): void
    {
        parent::setUp();
        $aux = $this->auxiliares();

        $this->console = Console::create([
            'nome' => 'PS2', 'quantidade' => 15,
            'plataformaConsole' => $aux['plataforma'], 'estado' => $aux['usado'], 'leitor' => $aux['digital'],
            'cores' => $aux['cor'], 'vintage' => $aux['retro'], 'aberto' => $aux['desbloqueado'],
            'colecionador' => $aux['edicao_especial'],
        ]);
    }

    /** Altera como se fosse um usuário logado e devolve a linha de histórico gerada */
    private function alterarComo(User $usuario, array $dados): ConsoleHistorico
    {
        $this->actingAs($usuario);
        $this->console->refresh()->update($dados);

        return ConsoleHistorico::latest('id')->first();
    }

    public function test_criacao_gera_historico(): void
    {
        $this->assertDatabaseHas('console_historico', [
            'console_id' => $this->console->id, 'acao' => 'criado',
        ]);
    }

    public function test_alteracao_grava_campo_valores_e_nome_do_usuario(): void
    {
        $ana = User::factory()->create(['name' => 'Ana']);

        $linha = $this->alterarComo($ana, ['quantidade' => 17]);

        $this->assertSame('atualizado', $linha->acao);
        $this->assertSame('quantidade', $linha->campo);
        $this->assertSame('15', (string) $linha->valor_anterior);
        $this->assertSame('17', (string) $linha->valor_novo);
        $this->assertSame('Ana', $linha->usuario);
    }

    public function test_alterar_dois_campos_gera_duas_linhas(): void
    {
        $this->alterarComo($this->funcionario(), ['quantidade' => 20, 'nome' => 'PS2 Slim']);

        $this->assertSame(2, ConsoleHistorico::where('acao', 'atualizado')->count());
    }

    /** O cenário pedido: 14:30 A muda 15->17, 14:35 B muda 17->19, o certo era 17 */
    public function test_desfazer_a_alteracao_errada_volta_para_17(): void
    {
        $this->alterarComo(User::factory()->create(['name' => 'A']), ['quantidade' => 17]);
        $errada = $this->alterarComo(User::factory()->create(['name' => 'B']), ['quantidade' => 19]);

        $this->actingAs($this->funcionario())
            ->post(route('historico.desfazer', ['console', $errada->id]))
            ->assertSessionHas('success');

        $this->assertSame(17, (int) $this->console->refresh()->quantidade);
        $this->assertSame('revertido', ConsoleHistorico::latest('id')->first()->acao);
    }

    public function test_restaurar_ate_aqui_desfaz_tudo_que_veio_depois(): void
    {
        $certa = $this->alterarComo($this->funcionario(), ['quantidade' => 17]);
        $this->alterarComo($this->funcionario(), ['quantidade' => 19]);
        $this->alterarComo($this->funcionario(), ['nome' => 'Nome errado']);

        $this->actingAs($this->funcionario())
            ->post(route('historico.restaurar', ['console', $certa->id]))
            ->assertSessionHas('success');

        $this->console->refresh();
        $this->assertSame(17, (int) $this->console->quantidade);
        $this->assertSame('PS2', $this->console->nome);
    }

    public function test_desfazer_e_bloqueado_se_o_campo_mudou_depois(): void
    {
        $antiga = $this->alterarComo($this->funcionario(), ['quantidade' => 17]);
        $this->alterarComo($this->funcionario(), ['quantidade' => 19]);

        $this->actingAs($this->funcionario())
            ->post(route('historico.desfazer', ['console', $antiga->id]))
            ->assertSessionHas('error');

        $this->assertSame(19, (int) $this->console->refresh()->quantidade);
    }

    public function test_reversao_pode_ser_desfeita(): void
    {
        $this->alterarComo($this->funcionario(), ['quantidade' => 17]);
        $errada = $this->alterarComo($this->funcionario(), ['quantidade' => 19]);

        $this->actingAs($this->funcionario())->post(route('historico.desfazer', ['console', $errada->id]));
        $reversao = ConsoleHistorico::latest('id')->first();
        $this->actingAs($this->funcionario())->post(route('historico.desfazer', ['console', $reversao->id]));

        $this->assertSame(19, (int) $this->console->refresh()->quantidade);
    }

    public function test_campo_imagem_nao_e_revertido(): void
    {
        $linha = $this->alterarComo($this->funcionario(), ['imagem' => 'consoles/x.jpg']);

        $this->actingAs($this->funcionario())
            ->post(route('historico.desfazer', ['console', $linha->id]))
            ->assertSessionHas('error');
    }

    public function test_tipo_invalido_da_404(): void
    {
        $this->actingAs($this->funcionario())
            ->post(route('historico.desfazer', ['usuario', 1]))
            ->assertNotFound();
    }

    public function test_reverter_exige_login(): void
    {
        $linha = ConsoleHistorico::first();

        $this->post(route('historico.desfazer', ['console', $linha->id]))->assertRedirect('/login');
    }

    public function test_excluir_item_com_historico_funciona(): void
    {
        $this->alterarComo($this->funcionario(), ['quantidade' => 17]);

        $this->actingAs($this->funcionario())
            ->delete("/console/{$this->console->id}")
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('console', ['id' => $this->console->id]);
    }

    public function test_historico_sobrevive_a_exclusao_e_registra_quem_excluiu(): void
    {
        $this->alterarComo($this->funcionario(), ['quantidade' => 17]);
        $joao = User::factory()->create(['name' => 'João']);

        $this->actingAs($joao)->delete("/console/{$this->console->id}");

        // criado + atualizado + excluido continuam lá
        $this->assertSame(3, ConsoleHistorico::where('console_id', $this->console->id)->count());
        $this->assertDatabaseHas('console_historico', [
            'console_id' => $this->console->id, 'acao' => 'excluido', 'usuario' => 'João',
        ]);

        // e o painel mostra a exclusão com o nome que o item tinha
        $this->actingAs($joao)->get('/colecao')->assertSee('"PS2" — excluido', false);
    }

    public function test_excluir_jogo_com_historico_funciona(): void
    {
        $aux  = $this->auxiliares();
        $jogo = Jogo::create([
            'nome' => 'Zelda', 'quantidade' => 1, 'Plataforma' => $aux['plataforma'],
            'estado' => $aux['usado'], 'vintage' => $aux['retro'], 'colecionador' => $aux['edicao_especial'],
        ]);
        $this->assertSame(1, JogoHistorico::where('jogo_id', $jogo->id)->count());

        $this->actingAs($this->funcionario())
            ->delete("/jogo/{$jogo->id}")
            ->assertRedirect(route('jogo.index'));

        $this->assertDatabaseMissing('jogo', ['id' => $jogo->id]);
    }

    public function test_modal_mostra_botoes_de_reverter(): void
    {
        $this->alterarComo($this->funcionario(), ['quantidade' => 17]);

        $this->actingAs($this->funcionario())->get('/console')
            ->assertSee('Desfazer')->assertSee('Restaurar até aqui');
    }
}
