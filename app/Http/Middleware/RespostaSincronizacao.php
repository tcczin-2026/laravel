<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Atende as alterações que ficaram guardadas no celular/navegador enquanto ele
 * estava offline (veja public/js/offline.js).
 *
 * Quando a conexão volta, o navegador reenvia cada alteração para a MESMA rota
 * do formulário (console.store, jogo.update, ...), com o cabeçalho
 * "X-Offline-Sync". Os controllers continuam iguais; este middleware só troca a
 * resposta de "redirect + mensagem na sessão" por um JSON que o script entende:
 *
 *   200 {ok: true,  mensagem}          gravado no banco
 *   409 {ok: false, mensagem}          o controller recusou (ex: item em uso)
 *   422 {message, errors}              validação falhou (o próprio Laravel monta)
 *   401 / 419                          precisa entrar de novo / token expirado
 *
 * Cada alteração tem um id único ("X-Offline-Id"). Se ela chegar duas vezes
 * (a conexão caiu antes da resposta voltar), a segunda vez não grava de novo.
 */
class RespostaSincronizacao
{
    public const CABECALHO = 'X-Offline-Sync';
    public const CABECALHO_ID = 'X-Offline-Id';

    /** Por quanto tempo lembrar das alterações já gravadas (evita duplicar) */
    private const LEMBRAR_POR_DIAS = 30;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasHeader(self::CABECALHO) || $request->isMethod('GET')) {
            return $next($request);
        }

        $id = $this->idValido($request->header(self::CABECALHO_ID));
        $chave = $id ? "offline-sync:{$request->user()?->id}:{$id}" : null;

        if ($chave && ($jaFeito = Cache::get($chave))) {
            return response()->json($jaFeito + ['repetido' => true]);
        }

        $resposta = $next($request);

        if (! $resposta instanceof RedirectResponse) {
            return $resposta; // 422, 401, 419, 404... já vêm prontos
        }

        if ($this->mandouProLogin($resposta)) {
            return response()->json(['ok' => false, 'mensagem' => 'Entre no sistema de novo para sincronizar.'], 401);
        }

        $sessao = $request->session();
        $erro = $sessao->get('error');
        $sucesso = $sessao->get('success');
        $sessao->forget(['error', 'success', 'errors', '_old_input']); // não aparecer na próxima página

        if ($erro) {
            return response()->json(['ok' => false, 'mensagem' => $erro], 409);
        }

        $resultado = ['ok' => true, 'mensagem' => $sucesso ?: 'Alteração sincronizada.'];

        if ($chave) {
            Cache::put($chave, $resultado, now()->addDays(self::LEMBRAR_POR_DIAS));
        }

        return response()->json($resultado);
    }

    private function idValido(?string $id): ?string
    {
        return $id && preg_match('/^[A-Za-z0-9-]{8,64}$/', $id) ? $id : null;
    }

    private function mandouProLogin(RedirectResponse $resposta): bool
    {
        return rtrim($resposta->getTargetUrl(), '/') === rtrim(route('login'), '/');
    }
}
