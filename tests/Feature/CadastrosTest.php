<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * CRUD, validação, imagem, paginação, estoque e filtros das 4 tabelas
 * principais. Cada teste roda uma vez para console, controle, jogo e acessorio.
 */
class CadastrosTest extends TestCase
{
    use RefreshDatabase;

    /** campo do formulário => tabela auxiliar de onde vem o id */
    private const ENTIDADES = [
        'console' => [
            'plataformaConsole' => 'plataforma', 'estado' => 'usado', 'leitor' => 'digital', 'cores' => 'cor',
            'vintage' => 'retro', 'aberto' => 'desbloqueado', 'colecionador' => 'edicao_especial',
        ],
        'controle' => [
            'plataformaControle' => 'plataforma', 'estado' => 'usado', 'cores' => 'cor',
            'vintage' => 'retro', 'colecionador' => 'edicao_especial',
        ],
        'jogo' => [
            'Plataforma' => 'plataforma', 'estado' => 'usado', 'vintage' => 'retro', 'colecionador' => 'edicao_especial',
        ],
        'acessorio' => [
            'Plataforma' => 'plataforma', 'estado' => 'usado', 'cores' => 'cor',
            'vintage' => 'retro', 'colecionador' => 'edicao_especial',
        ],
    ];

    /** Nome do campo de plataforma que o formulário de FILTRO de cada tela envia */
    private const FILTRO_PLATAFORMA = [
        'console'   => 'plataformaConsole',
        'controle'  => 'plataformacontrole',
        'jogo'      => 'plataformajogo',
        'acessorio' => 'plataforma',
    ];

    public static function entidades(): array
    {
        return array_map(fn ($e) => [$e], array_combine(array_keys(self::ENTIDADES), array_keys(self::ENTIDADES)));
    }

    private array $aux;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aux = $this->auxiliares();
    }

    private function dados(string $entidade, array $extra = []): array
    {
        $dados = ['nome' => "Item {$entidade}", 'quantidade' => 5];
        foreach (self::ENTIDADES[$entidade] as $campo => $tabela) {
            $dados[$campo] = $this->aux[$tabela];
        }

        return array_merge($dados, $extra);
    }

    private function criar(string $entidade, array $extra = []): int
    {
        return DB::table($entidade)->insertGetId($this->dados($entidade, $extra));
    }

    // ---------------------------------------------------------------- CRUD

    #[DataProvider('entidades')]
    public function test_funcionario_cadastra(string $entidade): void
    {
        $this->actingAs($this->funcionario())
            ->post("/{$entidade}", $this->dados($entidade, ['nome' => 'Novo item']))
            ->assertRedirect(route("{$entidade}.index"))
            ->assertSessionHas('success');

        $this->assertDatabaseHas($entidade, ['nome' => 'Novo item', 'quantidade' => 5]);
    }

    #[DataProvider('entidades')]
    public function test_funcionario_edita(string $entidade): void
    {
        $id = $this->criar($entidade);

        $this->actingAs($this->funcionario())->get("/{$entidade}/{$id}/edit")->assertOk();

        $this->actingAs($this->funcionario())
            ->put("/{$entidade}/{$id}", $this->dados($entidade, ['nome' => 'Editado', 'quantidade' => 9]))
            ->assertRedirect(route("{$entidade}.index"));

        $this->assertDatabaseHas($entidade, ['id' => $id, 'nome' => 'Editado', 'quantidade' => 9]);
    }

    #[DataProvider('entidades')]
    public function test_funcionario_exclui(string $entidade): void
    {
        $id = $this->criar($entidade);

        $this->actingAs($this->funcionario())
            ->delete("/{$entidade}/{$id}")
            ->assertRedirect(route("{$entidade}.index"))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing($entidade, ['id' => $id]);
    }

    #[DataProvider('entidades')]
    public function test_editar_ou_excluir_id_inexistente_da_404(string $entidade): void
    {
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->get("/{$entidade}/999/edit")->assertNotFound();
        $this->actingAs($usuario)->delete("/{$entidade}/999")->assertNotFound();
    }

    // ----------------------------------------------------------- validação

    #[DataProvider('entidades')]
    public function test_campos_obrigatorios(string $entidade): void
    {
        $this->actingAs($this->funcionario())
            ->post("/{$entidade}", [])
            ->assertSessionHasErrors(array_merge(['nome', 'quantidade'], array_keys(self::ENTIDADES[$entidade])));

        $this->assertDatabaseCount($entidade, 0);
    }

    #[DataProvider('entidades')]
    public function test_quantidade_negativa_e_recusada(string $entidade): void
    {
        $this->actingAs($this->funcionario())
            ->post("/{$entidade}", $this->dados($entidade, ['quantidade' => -1]))
            ->assertSessionHasErrors('quantidade');
    }

    #[DataProvider('entidades')]
    public function test_chave_estrangeira_inexistente_e_recusada(string $entidade): void
    {
        $campo = array_key_first(self::ENTIDADES[$entidade]); // o campo de plataforma

        $this->actingAs($this->funcionario())
            ->post("/{$entidade}", $this->dados($entidade, [$campo => 999]))
            ->assertSessionHasErrors($campo);
    }

    #[DataProvider('entidades')]
    public function test_nome_com_mais_de_100_caracteres_e_recusado(string $entidade): void
    {
        $this->actingAs($this->funcionario())
            ->post("/{$entidade}", $this->dados($entidade, ['nome' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('nome');
    }

    // -------------------------------------------------------------- imagem

    /** PNG real de 1x1 pixel (não depende da extensão GD, que o XAMPP não tem) */
    private function imagem(string $nome): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nome, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        ));
    }

    #[DataProvider('entidades')]
    public function test_upload_de_imagem(string $entidade): void
    {
        Storage::fake('public');

        $this->actingAs($this->funcionario())->post("/{$entidade}", $this->dados($entidade, [
            'imagem' => $this->imagem('foto.png'),
        ]));

        $caminho = DB::table($entidade)->value('imagem');
        $this->assertNotNull($caminho);
        Storage::disk('public')->assertExists($caminho);
    }

    #[DataProvider('entidades')]
    public function test_trocar_imagem_apaga_a_antiga(string $entidade): void
    {
        Storage::fake('public');
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->post("/{$entidade}", $this->dados($entidade, [
            'imagem' => $this->imagem('velha.png'),
        ]));
        $id     = DB::table($entidade)->value('id');
        $antiga = DB::table($entidade)->value('imagem');

        $this->actingAs($usuario)->put("/{$entidade}/{$id}", $this->dados($entidade, [
            'imagem' => $this->imagem('nova.png'),
        ]));

        Storage::disk('public')->assertMissing($antiga);
        Storage::disk('public')->assertExists(DB::table($entidade)->value('imagem'));
    }

    #[DataProvider('entidades')]
    public function test_arquivo_que_nao_e_imagem_e_recusado(string $entidade): void
    {
        Storage::fake('public');

        $this->actingAs($this->funcionario())->post("/{$entidade}", $this->dados($entidade, [
            'imagem' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ]))->assertSessionHasErrors('imagem');
    }

    // --------------------------------------------- listagem / paginação

    #[DataProvider('entidades')]
    public function test_listagem_pagina_de_10_em_10(string $entidade): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->criar($entidade, ['nome' => sprintf('Item %02d', $i)]);
        }
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->get("/{$entidade}")
            ->assertOk()->assertSee('Item 10')->assertDontSee('Item 11')->assertSee('pagination');

        $this->actingAs($usuario)->get("/{$entidade}?page=2")
            ->assertOk()->assertSee('Item 12')->assertDontSee('Item 01');
    }

    #[DataProvider('entidades')]
    public function test_etiqueta_de_estoque(string $entidade): void
    {
        $this->criar($entidade, ['nome' => 'Muito', 'quantidade' => 11]);
        $this->criar($entidade, ['nome' => 'Pouco', 'quantidade' => 10]);
        $this->criar($entidade, ['nome' => 'Zero', 'quantidade' => 0]);

        $html = $this->actingAs($this->funcionario())->get("/{$entidade}")->getContent();

        $this->assertSame(1, substr_count($html, 'stock-pill--ok'), '11 unidades = Em estoque');
        $this->assertSame(1, substr_count($html, 'stock-pill--low'), '10 unidades = Pouco restante');
        $this->assertSame(1, substr_count($html, 'stock-pill--out'), '0 unidades = Esgotado');
    }

    // -------------------------------------------------------------- filtros

    #[DataProvider('entidades')]
    public function test_filtro_por_nome(string $entidade): void
    {
        $this->criar($entidade, ['nome' => 'Super Mario']);
        $this->criar($entidade, ['nome' => 'Sonic']);

        $this->actingAs($this->funcionario())->get("/{$entidade}?nome=mario")
            ->assertOk()->assertSee('Super Mario')->assertDontSee('Sonic');
    }

    #[DataProvider('entidades')]
    public function test_filtro_por_plataforma_como_o_formulario_envia(string $entidade): void
    {
        $outra = DB::table('plataforma')->insertGetId(['nome' => 'Outra']);
        $campo = array_key_first(self::ENTIDADES[$entidade]);

        $this->criar($entidade, ['nome' => 'Da plataforma teste']);
        $this->criar($entidade, ['nome' => 'Da outra', $campo => $outra]);

        $this->actingAs($this->funcionario())
            ->get("/{$entidade}?" . self::FILTRO_PLATAFORMA[$entidade] . '=' . $this->aux['plataforma'])
            ->assertOk()->assertSee('Da plataforma teste')->assertDontSee('Da outra');
    }

    #[DataProvider('entidades')]
    public function test_filtro_por_edicao_especial_e_retro(string $entidade): void
    {
        $outraEdicao = DB::table('edicao_especial')->insertGetId(['nome' => 'Outra edicao']);
        $outroRetro  = DB::table('retro')->insertGetId(['nome' => 'Outro retro']);

        $this->criar($entidade, ['nome' => 'Edicao normal']);
        $this->criar($entidade, ['nome' => 'Edicao rara', 'colecionador' => $outraEdicao]);
        $this->criar($entidade, ['nome' => 'Retro antigo', 'vintage' => $outroRetro]);
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->get("/{$entidade}?edicoes={$outraEdicao}")
            ->assertSee('Edicao rara')->assertDontSee('Edicao normal')->assertDontSee('Retro antigo');

        $this->actingAs($usuario)->get("/{$entidade}?retros={$outroRetro}")
            ->assertSee('Retro antigo')->assertDontSee('Edicao normal')->assertDontSee('Edicao rara');
    }

    public function test_console_filtra_por_leitor_e_desbloqueado(): void
    {
        $outroLeitor = DB::table('digital')->insertGetId(['nome' => 'Outro leitor']);
        $outroAberto = DB::table('desbloqueado')->insertGetId(['nome' => 'Outro aberto']);

        $this->criar('console', ['nome' => 'Console padrao']);
        $this->criar('console', ['nome' => 'Console digital', 'leitor' => $outroLeitor]);
        $this->criar('console', ['nome' => 'Console aberto', 'aberto' => $outroAberto]);
        $usuario = $this->funcionario();

        $this->actingAs($usuario)->get("/console?digitais={$outroLeitor}")
            ->assertSee('Console digital')->assertDontSee('Console padrao')->assertDontSee('Console aberto');

        $this->actingAs($usuario)->get("/console?desbloqueados={$outroAberto}")
            ->assertSee('Console aberto')->assertDontSee('Console padrao')->assertDontSee('Console digital');
    }

    #[DataProvider('entidades')]
    public function test_filtro_mantem_parametros_na_paginacao(string $entidade): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->criar($entidade, ['nome' => "Mario {$i}"]);
        }

        $this->actingAs($this->funcionario())->get("/{$entidade}?nome=Mario")
            ->assertSee('nome=Mario&amp;page=2', false);
    }

    // ---------------------------------------------------------------- show

    public static function entidadesComShow(): array
    {
        return [['console'], ['jogo'], ['acessorio']];
    }

    #[DataProvider('entidadesComShow')]
    public function test_rota_de_detalhes_abre(string $entidade): void
    {
        $id = $this->criar($entidade);

        $this->actingAs($this->funcionario())->get("/{$entidade}/{$id}")->assertOk();
    }
}
