<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Libera a rota só para os perfis informados.
 * Uso nas rotas: ->middleware('perfil:gerente')
 */
class PerfilMiddleware
{
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        if (! $request->user() || ! in_array($request->user()->perfil, $perfis, true)) {
            return redirect()
                ->route('painel.index')
                ->with('error', 'Você não tem permissão para acessar essa área.');
        }

        return $next($request);
    }
}
