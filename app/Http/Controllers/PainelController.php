<?php

namespace App\Http\Controllers;

use App\Models\Acessorio;
use App\Models\Console;
use App\Models\Controle;
use App\Models\Jogo;

class PainelController extends Controller
{
    /** Tela inicial da colecao com os totais de cada tabela */
    public function index()
    {
        return view('painel.index', [
            'totais' => [
                'console'   => Console::count(),
                'controle'  => Controle::count(),
                'jogo'      => Jogo::count(),
                'acessorio' => Acessorio::count(),
            ],
            'auxiliares' => AuxiliarController::tipos(),
        ]);
    }
}
