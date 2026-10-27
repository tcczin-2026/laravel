<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Dados FIXOS para os testes do Cypress (cypress/e2e/filtros.cy.js).
 * Lê cypress/fixtures/filtros.json — o mesmo arquivo que os testes usam
 * pra saber o que cada filtro deve mostrar.
 *
 * Só roda no ambiente do Cypress (SQLite próprio) ou nos testes do PHPUnit:
 *     npm run cypress:preparar
 */
class CypressSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('cypress', 'testing') || config('database.default') !== 'sqlite') {
            throw new RuntimeException('O CypressSeeder só roda no ambiente do Cypress (SQLite). Use: npm run cypress:preparar');
        }

        $this->call(AuxiliaresSeeder::class);

        $dados = json_decode(file_get_contents(base_path('cypress/fixtures/filtros.json')), true, flags: JSON_THROW_ON_ERROR);

        User::updateOrCreate(['email' => $dados['usuario']['email']], [
            'name' => $dados['usuario']['nome'], 'perfil' => 'funcionario', 'password' => $dados['usuario']['senha'],
        ]);

        foreach ($dados['telas'] as $tabela => $tela) {
            DB::table($tabela)->delete();

            $itens = $tela['itens'];
            if (isset($tela['extras'])) {
                for ($i = 1; $i <= $tela['extras']['quantos']; $i++) {
                    $itens[] = ['nome' => sprintf('%s %02d', $tela['extras']['prefixo'], $i)] + $tela['extras']['atributos'];
                }
            }

            foreach ($itens as $item) {
                $linha = ['nome' => $item['nome'], 'quantidade' => $item['quantidade'] ?? 5];

                // "plataforma" => "xbox"  vira  "plataformaConsole" => 2
                foreach ($tela['colunas'] as $campo => [$coluna, $tabelaAux]) {
                    $linha[$coluna] = $this->idDe($tabelaAux, $item[$campo]);
                }

                DB::table($tabela)->insert($linha);
            }
        }
    }

    private function idDe(string $tabela, string $nome): int
    {
        return (int) DB::table($tabela)->where('nome', $nome)->value('id')
            ?: throw new RuntimeException("\"{$nome}\" não existe na tabela {$tabela} (veja o AuxiliaresSeeder).");
    }
}
