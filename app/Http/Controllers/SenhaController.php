<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * "Esqueci minha senha":
 * 1. a pessoa informa o e-mail -> enviamos um link com um token (válido por 60 min)
 * 2. ela clica no link -> escolhe a nova senha -> volta pro login
 *
 * Os tokens ficam na tabela password_reset_tokens (já existe no projeto).
 */
class SenhaController extends Controller
{
    /** Tela "esqueci minha senha" */
    public function pedir()
    {
        return view('auth.esqueci-senha');
    }

    /** Envia o e-mail com o link */
    public function enviarLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            // Normalmente é o SMTP do .env errado ou fora do ar
            Log::error('Falha ao enviar e-mail de redefinição de senha: ' . $e->getMessage());

            return back()->withInput()
                ->withErrors(['email' => 'Não foi possível enviar o e-mail agora. Avise o gerente.']);
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput()
                ->withErrors(['email' => 'Aguarde um minuto antes de pedir outro link.']);
        }

        // Mesma mensagem se o e-mail existir ou não, pra ninguém usar
        // essa tela para descobrir quais e-mails estão cadastrados.
        return back()->with('status', 'Se este e-mail estiver cadastrado, você vai receber um link para criar uma nova senha.');
    }

    /** Tela aberta pelo link do e-mail */
    public function formulario(Request $request, string $token)
    {
        return view('auth.redefinir-senha', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Salva a nova senha */
    public function redefinir(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ], [], ['password' => 'senha']);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $usuario, string $senha) {
                $usuario->password = $senha; // o cast 'hashed' do model criptografa
                $usuario->setRememberToken(Str::random(60)); // derruba o "lembrar de mim" antigo
                $usuario->save();

                event(new PasswordReset($usuario));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Este link é inválido ou já expirou. Peça um novo.']);
        }

        return redirect()->route('login')
            ->with('status', 'Senha alterada! Entre com a nova senha.');
    }
}
