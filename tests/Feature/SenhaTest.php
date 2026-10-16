<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RedefinirSenha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/** Esqueci minha senha */
class SenhaTest extends TestCase
{
    use RefreshDatabase;

    public function test_telas_abrem(): void
    {
        $this->get('/esqueci-senha')->assertOk()->assertSee('Enviar link');
        $this->get('/redefinir-senha/qualquer?email=a@b.com')->assertOk()->assertSee('Nova senha');
    }

    public function test_pedido_envia_email_para_quem_existe(): void
    {
        Notification::fake();
        $usuario = User::factory()->create();

        $this->post('/esqueci-senha', ['email' => $usuario->email])->assertSessionHas('status');

        Notification::assertSentTo($usuario, RedefinirSenha::class);
    }

    public function test_email_inexistente_recebe_a_mesma_mensagem_e_nada_e_enviado(): void
    {
        Notification::fake();

        $this->post('/esqueci-senha', ['email' => 'ninguem@teste.com'])->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_conteudo_do_email(): void
    {
        $usuario = User::factory()->create(['name' => 'Ana']);
        $email   = (new RedefinirSenha('tok123'))->toMail($usuario);

        $this->assertSame('Redefinição de senha - Colecao de Games', $email->subject);
        $this->assertStringContainsString('/redefinir-senha/tok123', $email->actionUrl);
        $this->assertStringContainsString('Ana', $email->greeting);
    }

    public function test_link_do_email_troca_a_senha(): void
    {
        $usuario = User::factory()->create(['password' => 'antiga123']);
        $token   = Password::createToken($usuario);

        $this->post('/redefinir-senha', [
            'token' => $token, 'email' => $usuario->email,
            'password' => 'nova12345', 'password_confirmation' => 'nova12345',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('nova12345', $usuario->refresh()->password));
    }

    public function test_link_nao_funciona_duas_vezes(): void
    {
        $usuario = User::factory()->create();
        $token   = Password::createToken($usuario);
        $dados   = ['token' => $token, 'email' => $usuario->email, 'password' => 'nova12345', 'password_confirmation' => 'nova12345'];

        $this->post('/redefinir-senha', $dados);
        $this->post('/redefinir-senha', array_merge($dados, ['password' => 'outra123', 'password_confirmation' => 'outra123']))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('nova12345', $usuario->refresh()->password));
    }

    public function test_token_falso_e_recusado(): void
    {
        $usuario = User::factory()->create(['password' => 'antiga123']);

        $this->post('/redefinir-senha', [
            'token' => 'falso', 'email' => $usuario->email,
            'password' => 'nova12345', 'password_confirmation' => 'nova12345',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('antiga123', $usuario->refresh()->password));
    }

    public function test_link_expirado_e_recusado(): void
    {
        $usuario = User::factory()->create(['password' => 'antiga123']);
        $token   = Password::createToken($usuario);

        $this->travel(61)->minutes();

        $this->post('/redefinir-senha', [
            'token' => $token, 'email' => $usuario->email,
            'password' => 'nova12345', 'password_confirmation' => 'nova12345',
        ])->assertSessionHasErrors('email');
    }

    public function test_segundo_pedido_seguido_e_bloqueado(): void
    {
        Notification::fake();
        $usuario = User::factory()->create();

        $this->post('/esqueci-senha', ['email' => $usuario->email]);
        $this->post('/esqueci-senha', ['email' => $usuario->email])->assertSessionHasErrors('email');

        Notification::assertSentToTimes($usuario, RedefinirSenha::class, 1);
    }

    public function test_falha_no_servidor_de_email_mostra_aviso_em_vez_de_erro(): void
    {
        $usuario = User::factory()->create();
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 1]);

        $this->post('/esqueci-senha', ['email' => $usuario->email])
            ->assertSessionHasErrors('email');
    }
}
