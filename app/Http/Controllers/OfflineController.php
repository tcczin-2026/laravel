<?php

namespace App\Http\Controllers;

use App\Models\Acessorio;
use App\Models\Console;
use App\Models\Controle;
use App\Models\Jogo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Rotas de apoio do modo offline (public/sw.js e public/js/offline.js).
 */
class OfflineController extends Controller
{
    /** Modelos principais => se a tela tem página de detalhes (show) */
    private const PRINCIPAIS = [
        'console'   => [Console::class, true],
        'controle'  => [Controle::class, false],
        'jogo'      => [Jogo::class, true],
        'acessorio' => [Acessorio::class, true],
    ];

    /** "Tem conexão com o servidor?" O navegador nunca guarda essa resposta. */
    public function ping(): Response
    {
        return response()->noContent()->header('Cache-Control', 'no-store');
    }

    /** Token CSRF atual e quem está logado (usado antes de reenviar a fila) */
    public function token(Request $request): JsonResponse
    {
        return response()->json([
            'token'   => csrf_token(),
            'usuario' => $request->user()->id,
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * Páginas que o aparelho baixa e guarda para abrir sem internet.
     * É assim que os dados do banco "descem" para o celular: sempre que há
     * conexão, essas páginas são baixadas de novo com os dados atuais.
     */
    public function paginas(Request $request): JsonResponse
    {
        $urls = [route('painel.index')];

        foreach (self::PRINCIPAIS as $tipo => [$model, $temShow]) {
            $this->listagem($urls, route("{$tipo}.index"), $model::count());
            $urls[] = route("{$tipo}.create");

            foreach ($model::orderBy('id')->pluck('id') as $id) {
                $urls[] = route("{$tipo}.edit", $id);
                if ($temShow) {
                    $urls[] = route("{$tipo}.show", $id);
                }
            }
        }

        if ($request->user()->isGerente()) {
            $this->listagem($urls, route('usuario.index'), User::count());
            $urls[] = route('usuario.create');
            foreach (User::orderBy('id')->pluck('id') as $id) {
                $urls[] = route('usuario.edit', $id);
            }

            foreach (AuxiliarController::tipos() as $tipo => $config) {
                $urls[] = route('auxiliar.index', $tipo);
                $urls[] = route('auxiliar.create', $tipo);
                foreach ($config['model']::orderBy('id')->pluck('id') as $id) {
                    $urls[] = route('auxiliar.edit', [$tipo, $id]);
                }
            }
        }

        return response()->json([
            'usuario' => $request->user()->id,
            'urls'    => $urls,
        ])->header('Cache-Control', 'no-store');
    }

    /** Página 1 sem "?page" (é o link do menu) e as demais com "?page=N" */
    private function listagem(array &$urls, string $base, int $total): void
    {
        $urls[] = $base;
        $paginas = (int) ceil($total / self::POR_PAGINA[0]);

        for ($p = 2; $p <= $paginas; $p++) {
            $urls[] = "{$base}?page={$p}";
        }
    }
}
