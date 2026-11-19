<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /** Tentativas erradas permitidas (por e-mail + computador) antes de bloquear */
    public const MAX_TENTATIVAS = 5;

    /** Por quantos segundos fica bloqueado depois de errar demais */
    public const BLOQUEIO_SEGUNDOS = 60;

    /** Tela de login */
    public function create()
    {
        return view('auth.login');
    }

    /** Confere e-mail e senha e abre a sessão */
    public function store(Request $request)
    {
        $credenciais = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Evita ficar tentando adivinhar a senha (força bruta)
        $chave = Str::lower($credenciais['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($chave, self::MAX_TENTATIVAS)) {
            $segundos = RateLimiter::availableIn($chave);

            return back()
                ->withErrors(['email' => "Muitas tentativas erradas. Tente de novo em {$segundos} segundos."])
                ->onlyInput('email');
        }

        if (! Auth::attempt($credenciais, $request->boolean('lembrar'))) {
            RateLimiter::hit($chave, self::BLOQUEIO_SEGUNDOS);

            return back()
                ->withErrors(['email' => 'E-mail ou senha incorretos.'])
                ->onlyInput('email');
        }

        RateLimiter::clear($chave);
        $request->session()->regenerate(); // evita roubo de sessão (session fixation)

        return redirect()->intended(route('painel.index'));
    }

    /** Sai do sistema */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
