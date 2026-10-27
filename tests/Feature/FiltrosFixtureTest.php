<?php

namespace Tests\Feature;

use Database\Seeders\CypressSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Confere, pelo servidor, os mesmos casos que o Cypress testa no navegador
 * (cypress/fixtures/filtros.json). Se este passar e o Cypress falhar, o
 * problema está na tela (HTML/JS), não na lógica do filtro.
 */
class FiltrosFixtureTest extends TestCase
{
    use RefreshDatabase;

    private static function fixture(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/../../cypress/fixtures/filtros.json'), true);
    }

    /** Todos os itens da tela (incluindo os "extras") */
    private static function itens(array $tela): array
    {
        $itens = $tela['itens'];
        for ($i = 1; $i <= ($tela['extras']['quantos'] ?? 0); $i++) {
            $itens[] = ['nome' => sprintf('%s %02d', $tela['extras']['prefixo'], $i)] + $tela['extras']['atributos'];
        }

        return $itens;
    }

    /** Um caso por tela × filtro × valor que aparece nos dados */
    public static function casos(): array
    {
        $casos = [];
        foreach (self::fixture()['telas'] as $tipo => $tela) {
            foreach ($tela['filtros'] as $campo => $nomeNoForm) {
                foreach (array_unique(array_column(self::itens($tela), $campo)) as $valor) {
                    $casos["{$tipo} {$campo}={$valor}"] = [$tipo, [$campo => $valor]];
                }
            }
            $casos["{$tipo} combinado"] = [$tipo, $tela['combinacao']];
        }

        return $casos;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CypressSeeder::class);
    }

    #[DataProvider('casos')]
    public function test_filtro_mostra_exatamente_os_itens_esperados(string $tipo, array $filtros): void
    {
        $tela = self::fixture()['telas'][$tipo];

        $esperados = array_column(array_filter(self::itens($tela), function ($item) use ($filtros) {
            foreach ($filtros as $campo => $valor) {
                if ($item[$campo] !== $valor) {
                    return false;
                }
            }

            return true;
        }), 'nome');

        // monta a URL como o formulário do filtro manda (com o id de cada opção)
        $query = ['por_pagina' => 100];
        foreach ($filtros as $campo => $valor) {
            [, $tabelaAux] = $tela['colunas'][$campo];
            $query[$tela['filtros'][$campo]] = \DB::table($tabelaAux)->where('nome', $valor)->value('id');
        }

        $html = $this->actingAs($this->funcionario())->get("/{$tipo}?" . http_build_query($query))->assertOk()->getContent();

        preg_match_all('#<td class="col-nome">\s*<div class="fw-semibold">([^<]+)</div>#', $html, $m);
        $mostrados = array_map(fn ($n) => html_entity_decode(trim($n), ENT_QUOTES), $m[1]);

        sort($esperados);
        sort($mostrados);
        $this->assertSame($esperados, $mostrados, 'Filtro ' . json_encode($filtros));
    }

    public function test_busca_por_nome_ignora_maiusculas(): void
    {
        foreach (self::fixture()['telas'] as $tipo => $tela) {
            foreach ($tela['buscasNome'] as $termo) {
                $esperados = array_column(array_filter(self::itens($tela),
                    fn ($i) => stripos($i['nome'], $termo) !== false), 'nome');

                $resposta = $this->actingAs($this->funcionario())->get("/{$tipo}?por_pagina=100&nome=" . urlencode($termo));

                foreach ($esperados as $nome) {
                    $resposta->assertSee($nome);
                }
                if (! $esperados) {
                    $resposta->assertSee($tela['vazio']);
                }
            }
        }
    }
}
