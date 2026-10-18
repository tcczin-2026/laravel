<?php

namespace Tests\Feature;

use App\Models\Console;
use App\Models\ConsoleHistorico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Listagens e histórico compactos (sem barra de rolagem lateral) */
class LayoutListagemTest extends TestCase
{
    use RefreshDatabase;

    private Console $console;

    protected function setUp(): void
    {
        parent::setUp();
        $aux = $this->auxiliares();
        $this->console = Console::create([
            'nome' => 'Mega Drive', 'quantidade' => 5, 'valor' => 899.90, 'armazenamento' => 1000,
            'plataformaConsole' => $aux['plataforma'], 'estado' => $aux['usado'], 'leitor' => $aux['digital'],
            'cores' => $aux['cor'], 'vintage' => $aux['retro'], 'aberto' => $aux['desbloqueado'],
            'colecionador' => $aux['edicao_especial'],
        ]);
    }

    public static function telas(): array
    {
        return [['console'], ['controle'], ['jogo'], ['acessorio']];
    }

    #[DataProvider('telas')]
    public function test_todas_as_listagens_usam_a_tabela_compacta(string $tela): void
    {
        $this->actingAs($this->funcionario())->get("/{$tela}")
            ->assertOk()->assertSee('tabela-lista')->assertSee('Características');
    }

    public function test_linha_tem_nome_estoque_caracteristicas_e_botoes_empilhados(): void
    {
        $this->actingAs($this->funcionario())->get('/console')
            ->assertSeeInOrder(['Mega Drive', '#' . $this->console->id, 'R$ 899,90', '5 · Pouco restante'])
            ->assertSeeInOrder(['class="chip"', '1 TB'], false)
            ->assertSeeInOrder(['class="acoes"', 'Ver', 'Editar', 'Excluir'], false);
    }

    public function test_excluir_pela_listagem_continua_funcionando(): void
    {
        $this->actingAs($this->funcionario())->get('/console')
            ->assertSee(route('console.destroy', $this->console->id), false)
            ->assertSee('Excluir este console?');
    }

    public function test_historico_compacto_com_usuario_e_reverter(): void
    {
        $this->actingAs($this->funcionario());
        $this->console->update(['quantidade' => 7]);

        $this->get('/console')
            ->assertSeeInOrder(['tabela-historico', 'Quando', 'Alteração', 'Reverter'], false)
            ->assertSeeInOrder(['quantidade:', '5', '7', 'Desfazer'])
            ->assertDontSee('<th>Campo</th>', false);
    }

    public function test_troca_de_foto_nao_mostra_o_caminho_do_arquivo(): void
    {
        $this->actingAs($this->funcionario());
        $this->console->update(['imagem' => 'consoles/aBcDeF123.jpg']);

        $linha = ConsoleHistorico::where('campo', 'imagem')->first();
        $this->assertSame('sem foto', $linha->de_texto);
        $this->assertSame('nova foto', $linha->para_texto);
        $this->get('/console')->assertSeeInOrder(['tabela-historico', 'imagem:', 'sem foto', 'nova foto'], false);
    }
}
