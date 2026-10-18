<?php

namespace Tests\Feature;

use App\Models\Console;
use App\Models\ConsoleHistorico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Campo "valor" nas 4 tabelas principais e "armazenamento" nos consoles */
class ValorArmazenamentoTest extends TestCase
{
    use RefreshDatabase;

    private const CAMPOS = [
        'console'   => ['plataformaConsole' => 'plataforma', 'estado' => 'usado', 'leitor' => 'digital', 'cores' => 'cor',
                        'vintage' => 'retro', 'aberto' => 'desbloqueado', 'colecionador' => 'edicao_especial'],
        'controle'  => ['plataformaControle' => 'plataforma', 'estado' => 'usado', 'cores' => 'cor',
                        'vintage' => 'retro', 'colecionador' => 'edicao_especial'],
        'jogo'      => ['Plataforma' => 'plataforma', 'estado' => 'usado', 'vintage' => 'retro', 'colecionador' => 'edicao_especial'],
        'acessorio' => ['Plataforma' => 'plataforma', 'estado' => 'usado', 'cores' => 'cor',
                        'vintage' => 'retro', 'colecionador' => 'edicao_especial'],
    ];

    private array $aux;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aux = $this->auxiliares();
    }

    public static function entidades(): array
    {
        return [['console'], ['controle'], ['jogo'], ['acessorio']];
    }

    private function dados(string $entidade, array $extra = []): array
    {
        $dados = ['nome' => 'Item', 'quantidade' => 1];
        foreach (self::CAMPOS[$entidade] as $campo => $tabela) {
            $dados[$campo] = $this->aux[$tabela];
        }

        return array_merge($dados, $extra);
    }

    #[DataProvider('entidades')]
    public function test_cadastra_com_valor_e_mostra_em_reais(string $entidade): void
    {
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->post("/{$entidade}", $this->dados($entidade, ['valor' => '1299.90']))
            ->assertSessionHasNoErrors();

        $this->assertEquals(1299.90, (float) DB::table($entidade)->value('valor'));
        $this->actingAs($usuario)->get("/{$entidade}")->assertSee('R$ 1.299,90');
        $this->actingAs($usuario)->get("/{$entidade}/create")->assertSee('name="valor"', false);
    }

    #[DataProvider('entidades')]
    public function test_aceita_valor_no_formato_brasileiro(string $entidade): void
    {
        $this->actingAs($this->funcionario())->post("/{$entidade}", $this->dados($entidade, ['valor' => '1.299,90']));

        $this->assertEquals(1299.90, (float) DB::table($entidade)->value('valor'));
    }

    #[DataProvider('entidades')]
    public function test_valor_e_opcional(string $entidade): void
    {
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->post("/{$entidade}", $this->dados($entidade))->assertSessionHasNoErrors();

        $this->assertNull(DB::table($entidade)->value('valor'));
    }

    #[DataProvider('entidades')]
    public function test_valor_invalido_e_recusado(string $entidade): void
    {
        $usuario = $this->funcionario();

        foreach (['-5', 'abc', '100000000'] as $invalido) {
            $this->actingAs($usuario)->post("/{$entidade}", $this->dados($entidade, ['valor' => $invalido]))
                ->assertSessionHasErrors('valor');
        }
        $this->assertSame(0, DB::table($entidade)->count());
    }

    #[DataProvider('entidades')]
    public function test_edita_o_valor(string $entidade): void
    {
        $usuario = $this->funcionario();
        $this->actingAs($usuario)->post("/{$entidade}", $this->dados($entidade, ['valor' => '100']));
        $id = DB::table($entidade)->value('id');

        $this->actingAs($usuario)->get("/{$entidade}/{$id}/edit")->assertSee('value="100.00"', false);
        $this->actingAs($usuario)->put("/{$entidade}/{$id}", $this->dados($entidade, ['valor' => '89,90']));

        $this->assertEquals(89.90, (float) DB::table($entidade)->value('valor'));
    }

    public function test_console_com_armazenamento(): void
    {
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->post('/console', $this->dados('console', ['nome' => 'PS5', 'armazenamento' => 825]));
        $this->actingAs($usuario)->post('/console', $this->dados('console', ['nome' => 'Series X', 'armazenamento' => 1000]));
        $this->actingAs($usuario)->post('/console', $this->dados('console', ['nome' => 'Retro']));

        $html = $this->actingAs($usuario)->get('/console')->assertOk()->getContent();
        $this->assertStringContainsString('825 GB', $html);
        $this->assertStringContainsString('1 TB', $html);
        $this->assertNull(DB::table('console')->where('nome', 'Retro')->value('armazenamento'));
        $this->actingAs($usuario)->get('/console/create')->assertSee('name="armazenamento"', false);
    }

    public function test_armazenamento_formatado(): void
    {
        foreach ([null => '-', 64 => '64 GB', 825 => '825 GB', 1000 => '1 TB', 1500 => '1,5 TB', 2000 => '2 TB'] as $gb => $texto) {
            $this->assertSame($texto, (new Console(['armazenamento' => $gb ?: null]))->armazenamento_formatado);
        }
    }

    public function test_armazenamento_invalido_e_recusado(): void
    {
        $usuario = $this->funcionario();

        foreach (['0', '-1', '2.5', 'muito'] as $invalido) {
            $this->actingAs($usuario)->post('/console', $this->dados('console', ['armazenamento' => $invalido]))
                ->assertSessionHasErrors('armazenamento');
        }
    }

    public function test_historico_mostra_valor_em_reais_e_desfazer_funciona(): void
    {
        $usuario = $this->funcionario();
        $this->actingAs($usuario)->post('/console', $this->dados('console', ['valor' => '2299.90']));
        $console = Console::first();

        // gravado como "2199.9" (sem o zero final), igual alguém digitando
        $this->actingAs($usuario)->put("/console/{$console->id}", $this->dados('console', ['valor' => '2199.9']));

        $linha = ConsoleHistorico::where('campo', 'valor')->first();
        $this->assertSame('R$ 2.299,90', $linha->de_texto);
        $this->assertSame('R$ 2.199,90', $linha->para_texto);
        $this->actingAs($usuario)->get('/colecao')->assertSeeInOrder(['valor:', 'R$ 2.299,90', 'R$ 2.199,90']);

        // o "Desfazer" não pode achar que "2199.90" (banco) é diferente de "2199.9" (histórico)
        $this->actingAs($usuario)->post(route('historico.desfazer', ['console', $linha->id]))->assertSessionHas('success');
        $this->assertEquals(2299.90, (float) $console->refresh()->valor);
    }

    public function test_tabela_de_acessorios_usa_o_tema_escuro(): void
    {
        $this->actingAs($this->funcionario())->get('/acessorio')
            ->assertSee('<table class="table table-dark align-middle tabela-lista">', false);
    }
}
