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

class PainelController extends Controller
{
    /** Tela inicial da colecao com os totais de cada tabela */
    public function index()
    {
        $totais = [
            'console'   => Console::count(),
            'controle'  => Controle::count(),
            'jogo'      => Jogo::count(),
            'acessorio' => Acessorio::count(),
        ];

        $auxiliares = AuxiliarController::tipos();

        $historico = collect()
            ->merge($this->historicoDe(ConsoleHistorico::class, 'console', 'Console'))
            ->merge($this->historicoDe(ControleHistorico::class, 'controle', 'Controle'))
            ->merge($this->historicoDe(JogoHistorico::class, 'jogo', 'Jogo'))
            ->merge($this->historicoDe(AcessorioHistorico::class, 'acessorio', 'Acessório'))
            ->sortByDesc('quando')
            ->take(10)
            ->values();

        return view('painel.index', compact('totais', 'auxiliares', 'historico'));
    }

    private function historicoDe(string $model, string $relacao, string $tipo)
    {
        return $model::with($relacao)
            ->latest('created_at')
            ->take(10)
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