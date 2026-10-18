<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Paginação: 5 números, "ir para" e itens por página */
class PaginacaoTest extends TestCase
{
    use RefreshDatabase;

    private function criarJogos(int $quantos, string $prefixo = 'Jogo'): void
    {
        $aux = $this->auxiliares();
        $linhas = [];
        for ($i = 1; $i <= $quantos; $i++) {
            $linhas[] = [
                'nome' => sprintf('%s %03d', $prefixo, $i), 'quantidade' => 1, 'Plataforma' => $aux['plataforma'],
                'estado' => $aux['usado'], 'vintage' => $aux['retro'], 'colecionador' => $aux['edicao_especial'],
            ];
        }
        DB::table('jogo')->insert($linhas);
    }

    /** Números de página (e "…") na ordem em que aparecem, sem as setas */
    private function numeros(string $html): array
    {
        preg_match_all('#<(?:a|span) class="page-link"[^>]*>([^<]+)</(?:a|span)>#', $html, $m);

        return array_values(array_filter(
            array_map(fn ($t) => html_entity_decode(trim($t)), $m[1]),
            fn ($t) => ! in_array($t, ['‹', '›'], true)
        ));
    }

    public function test_mostra_primeira_ultima_e_vizinhas_da_atual(): void
    {
        $this->criarJogos(100); // 10 páginas

        $html = $this->actingAs($this->funcionario())->get('/jogo?page=5')->getContent();

        $this->assertSame(['1', '…', '4', '5', '6', '…', '10'], $this->numeros($html));
    }

    public function test_no_comeco_e_no_fim_nao_repete_numeros(): void
    {
        $this->criarJogos(100);
        $usuario = $this->funcionario();

        $this->assertSame(['1', '2', '…', '10'], $this->numeros($this->actingAs($usuario)->get('/jogo')->getContent()));
        $this->assertSame(['1', '…', '9', '10'], $this->numeros($this->actingAs($usuario)->get('/jogo?page=10')->getContent()));
        $this->assertSame(['1', '2', '3', '…', '10'], $this->numeros($this->actingAs($usuario)->get('/jogo?page=2')->getContent()));
    }

    public function test_com_poucas_paginas_mostra_todas(): void
    {
        $this->criarJogos(25); // 3 páginas

        $html = $this->actingAs($this->funcionario())->get('/jogo?page=2')->getContent();

        $this->assertSame(['1', '2', '3'], $this->numeros($html));
    }

    public function test_itens_por_pagina(): void
    {
        $this->criarJogos(60);
        $usuario = $this->funcionario();

        foreach ([25 => 'Jogo 025', 50 => 'Jogo 050'] as $porPagina => $ultimoVisivel) {
            $html = $this->actingAs($usuario)->get("/jogo?por_pagina={$porPagina}")->getContent();
            $this->assertStringContainsString($ultimoVisivel, $html);
            $this->assertStringNotContainsString(sprintf('Jogo %03d', $porPagina + 1), $html);
            $this->assertStringContainsString("<option value=\"{$porPagina}\" selected", $html);
        }

        // os links das páginas mantêm a escolha
        $this->actingAs($usuario)->get('/jogo?por_pagina=25')->assertSee('por_pagina=25&amp;page=2', false);
    }

    public function test_valor_invalido_de_itens_por_pagina_volta_para_10(): void
    {
        $this->criarJogos(30);

        $html = $this->actingAs($this->funcionario())->get('/jogo?por_pagina=9999')->getContent();

        $this->assertStringContainsString('Jogo 010', $html);
        $this->assertStringNotContainsString('Jogo 011', $html);
    }

    public function test_ir_para_pagina_mantem_filtros_e_itens_por_pagina(): void
    {
        $this->criarJogos(40, 'Mario');
        $usuario = $this->funcionario();

        $html = $this->actingAs($usuario)->get('/jogo?nome=Mario&por_pagina=25')->getContent();

        // o formulário "Ir para" leva junto o filtro e a quantidade por página
        $this->assertMatchesRegularExpression('#<input type="hidden" name="nome" value="Mario">.*?<input type="number" name="page"[^>]*max="2"#s', $html);
        $this->assertStringContainsString('<input type="hidden" name="por_pagina" value="25">', $html);

        // e enviar o formulário (GET ?page=2) mostra a página certa
        $this->actingAs($usuario)->get('/jogo?nome=Mario&por_pagina=25&page=2')
            ->assertSee('Mario 040')->assertDontSee('Mario 025');
    }

    public function test_com_poucos_itens_nao_mostra_paginacao(): void
    {
        $this->criarJogos(8);

        $this->actingAs($this->funcionario())->get('/jogo')
            ->assertDontSee('Itens por página')->assertDontSee('page-link');
    }

    public function test_itens_por_pagina_aparece_mesmo_com_uma_pagina_so(): void
    {
        $this->criarJogos(20); // com 25 por página cabe tudo numa página

        $this->actingAs($this->funcionario())->get('/jogo?por_pagina=25')
            ->assertSee('Itens por página')->assertDontSee('Ir para');
    }

    public function test_funciona_no_painel_e_em_usuarios(): void
    {
        $gerente = $this->gerente();
        \App\Models\User::factory()->count(30)->create();

        $this->actingAs($gerente)->get('/usuario?por_pagina=25')
            ->assertOk()->assertSee('<option value="25" selected', false)->assertSee('Ir para');

        $this->actingAs($gerente)->get('/colecao?por_pagina=50')->assertOk();
    }
}
