<?php

namespace App\Http\Controllers;

use App\Models\Acessorio;
use App\Models\Console;
use App\Models\Controle;
use App\Models\Jogo;
use App\Models\ConsoleHistorico;
use App\Models\ControleHistorico;
use App\Models\JogoHistorico;
use App\Models\AcessorioHistorico;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PainelController extends Controller
{
    /** Tela inicial da colecao com os totais de cada tabela */
    public function index(Request $request)
    {
        $totais = [
            'console'   => Console::count(),
            'controle'  => Controle::count(),
            'jogo'      => Jogo::count(),
            'acessorio' => Acessorio::count(),
        ];

        $auxiliares = AuxiliarController::tipos();

        // O histórico fica em 4 tabelas, então a paginação é montada na mão:
        // pega de cada tabela os registros até o fim da página atual,
        // junta, ordena por data e recorta só a fatia da página.
        $porPagina = 10;
        $pagina    = LengthAwarePaginator::resolveCurrentPage();
        $limite    = $pagina * $porPagina;

        $fontes = [
            [ConsoleHistorico::class, 'console', 'Console'],
            [ControleHistorico::class, 'controle', 'Controle'],
            [JogoHistorico::class, 'jogo', 'Jogo'],
            [AcessorioHistorico::class, 'acessorio', 'Acessório'],
        ];

        $itens = collect();
        $total = 0;
        foreach ($fontes as [$model, $relacao, $tipo]) {
            $itens = $itens->merge($this->historicoDe($model, $relacao, $tipo, $limite));
            $total += $model::count();
        }

        $historico = new LengthAwarePaginator(
            $itens->sortByDesc('quando')->slice(($pagina - 1) * $porPagina, $porPagina)->values(),
            $total,
            $porPagina,
            $pagina,
            ['path' => $request->url()]
        );

        return view('painel.index', compact('totais', 'auxiliares', 'historico'));
    }

    private function historicoDe(string $model, string $relacao, string $tipo, int $limite)
    {
        return $model::with($relacao)
            ->latest('created_at')
            ->take($limite)
            ->get()
            ->map(function ($h) use ($relacao, $tipo) {
                return [
                    'tipo'   => $tipo,
                    'nome'   => $h->$relacao->nome ?? $this->nomeDoResumo($h),
                    'acao'   => $h->acao,
                    'quando' => $h->created_at,
                ];
            });
    }

    private function nomeDoResumo($h): string
    {
        $resumo = $h->valor_novo ?? $h->valor_anterior ?? '';
        preg_match('/nome:\s*(.*?),/', $resumo, $m);
        return $m[1] ?? '(item excluído)';
    }
}