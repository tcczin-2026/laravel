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
    /** Tela inicial da colecao com os totais de cada tabela (só itens com estoque) */
    public function index(Request $request)
    {
        $totais = [
            'console'   => Console::where('quantidade', '>', 0)->count(),
            'controle'  => Controle::where('quantidade', '>', 0)->count(),
            'jogo'      => Jogo::where('quantidade', '>', 0)->count(),
            'acessorio' => Acessorio::where('quantidade', '>', 0)->count(),
        ];

        $auxiliares = AuxiliarController::tipos();

        // O histórico fica em 4 tabelas, então a paginação é montada na mão:
        // pega de cada tabela os registros até o fim da página atual,
        // junta, ordena por data e recorta só a fatia da página.
        $porPagina = $this->porPagina();
        $pagina    = LengthAwarePaginator::resolveCurrentPage();
        $limite    = $pagina * $porPagina;

        $fontes = [
            [ConsoleHistorico::class, 'console', 'Console'],
            [ControleHistorico::class, 'controle', 'Controle'],
            [JogoHistorico::class, 'jogo', 'Jogo'],
            [AcessorioHistorico::class, 'acessorio', 'Acessório'],
        ];

        // Busca pelo id do item (?id=5). Como cada tabela tem seus próprios ids,
        // dá pra escolher o tipo (?tipo=console) pra não misturar o console 5 com o jogo 5.
        $id = $request->filled('id') ? (int) $request->input('id') : null;
        $tipoEscolhido = $request->input('tipo');

        $itens = collect();
        $total = 0;
        foreach ($fontes as [$model, $relacao, $tipo]) {
            if ($tipoEscolhido && $tipoEscolhido !== $relacao) {
                continue;
            }
            $consulta = fn () => $model::query()->when($id, fn ($q) => $q->where("{$relacao}_id", $id));

            $itens = $itens->merge($this->historicoDe($consulta(), $relacao, $tipo, $limite));
            $total += $consulta()->count();
        }

        $historico = new LengthAwarePaginator(
            $itens->sortByDesc('quando')->slice(($pagina - 1) * $porPagina, $porPagina)->values(),
            $total,
            $porPagina,
            $pagina,
            ['path' => $request->url()]
        );
        $historico->withQueryString(); // mantém o ?por_pagina=, ?id= e ?tipo= ao trocar de página

        $tiposHistorico = array_column($fontes, 2, 1); // ['console' => 'Console', ...]

        return view('painel.index', compact('totais', 'auxiliares', 'historico', 'tiposHistorico'));
    }

    private function historicoDe($consulta, string $relacao, string $tipo, int $limite)
    {
        return $consulta->with($relacao)
            ->latest('created_at')
            ->take($limite)
            ->get()
            ->map(function ($h) use ($relacao, $tipo) {
                return [
                    'tipo'     => $tipo,
                    'id'       => $h->{"{$relacao}_id"},
                    'nome'     => $h->$relacao->nome ?? $this->nomeDoResumo($h),
                    // link pra listagem já filtrada pelo item (só se ele ainda existe)
                    'link'     => $h->$relacao ? route("{$relacao}.index", ['id' => $h->$relacao->id]) : null,
                    'acao'     => $h->acao,
                    'campo'    => $h->campo_rotulo, // nomes em vez de ids (trait FormataHistorico)
                    'de'       => $h->de_texto,
                    'para'     => $h->para_texto,
                    'usuario'  => $h->usuario ?? 'Sistema',
                    'quando'   => $h->created_at,
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