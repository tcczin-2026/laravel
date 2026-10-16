<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Login, logout e quem pode acessar o quê */
class SegurancaTest extends TestCase
{
    use RefreshDatabase;

    public static function paginasProtegidas(): array
    {
        return [
            ['/'], ['/colecao'], ['/console'], ['/controle'], ['/jogo'], ['/acessorio'],
            ['/usuario'], ['/auxiliar/cor'], ['/console/create'],
        ];
    }

    #[DataProvider('paginasProtegidas')]
    public function test_visitante_e_mandado_para_o_login(string $url): void
    {
        $this->get($url)->assertRedirect('/login');
    }

    public function test_tela_de_login_abre(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login')->assertSee('Esqueceu a senha?');
    }

    public function test_login_com_senha_certa_entra_no_painel(): void
    {
        $usuario = User::factory()->create(['password' => 'segredo123']);

        $this->post('/login', ['email' => $usuario->email, 'password' => 'segredo123'])
            ->assertRedirect(route('painel.index'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_login_com_senha_errada_e_recusado(): void
    {
        $usuario = User::factory()->create(['password' => 'segredo123']);

        $this->from('/login')
            ->post('/login', ['email' => $usuario->email, 'password' => 'errada'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_com_email_inexistente_e_recusado(): void
    {
        $this->post('/login', ['email' => 'ninguem@teste.com', 'password' => 'x'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_sem_preencher_campos_da_erro_de_validacao(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_quem_ja_esta_logado_nao_ve_a_tela_de_login(): void
    {
        $this->actingAs($this->funcionario())->get('/login')->assertRedirect('/colecao');
    }

    public function test_logout_encerra_a_sessao(): void
    {
        $this->actingAs($this->funcionario())->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    public static function paginasDeTodos(): array
    {
        return [['/colecao'], ['/console'], ['/controle'], ['/jogo'], ['/acessorio'],
                ['/console/create'], ['/controle/create'], ['/jogo/create'], ['/acessorio/create']];
    }

    #[DataProvider('paginasDeTodos')]
    public function test_funcionario_acessa_as_tabelas_principais(string $url): void
    {
        $this->actingAs($this->funcionario())->get($url)->assertOk();
    }

    #[DataProvider('paginasDeTodos')]
    public function test_gerente_acessa_as_tabelas_principais(string $url): void
    {
        $this->actingAs($this->gerente())->get($url)->assertOk();
    }

    public static function paginasDoGerente(): array
    {
        $paginas = [['/usuario'], ['/usuario/create']];
        foreach (['plataforma', 'cor', 'retro', 'usado', 'digital', 'desbloqueado', 'edicao_especial'] as $tipo) {
            $paginas[] = ["/auxiliar/{$tipo}"];
            $paginas[] = ["/auxiliar/{$tipo}/novo"];
        }

        return $paginas;
    }

    #[DataProvider('paginasDoGerente')]
    public function test_gerente_acessa_usuarios_e_auxiliares(string $url): void
    {
        $this->actingAs($this->gerente())->get($url)->assertOk();
    }

    #[DataProvider('paginasDoGerente')]
    public function test_funcionario_e_bloqueado_em_usuarios_e_auxiliares(string $url): void
    {
        $this->actingAs($this->funcionario())->get($url)
            ->assertRedirect(route('painel.index'))
            ->assertSessionHas('error');
    }

    public function test_funcionario_nao_consegue_criar_usuario_mandando_o_formulario_direto(): void
    {
        $this->actingAs($this->funcionario())->post('/usuario', [
            'name' => 'Invasor', 'email' => 'invasor@teste.com', 'perfil' => 'gerente',
            'password' => 'segredo1', 'password_confirmation' => 'segredo1',
        ])->assertRedirect(route('painel.index'));

        $this->assertDatabaseMissing('users', ['email' => 'invasor@teste.com']);
    }

    public function test_funcionario_nao_consegue_criar_registro_auxiliar_direto(): void
    {
        $this->actingAs($this->funcionario())->post('/auxiliar/cor', ['nome' => 'Hack'])
            ->assertRedirect(route('painel.index'));

        $this->assertDatabaseMissing('cor', ['nome' => 'Hack']);
    }

    public function test_menu_do_funcionario_esconde_areas_do_gerente(): void
    {
        $this->actingAs($this->funcionario())->get('/colecao')
            ->assertDontSee('Tabelas auxiliares')
            ->assertDontSee(route('usuario.index'))
            ->assertSee('Sair');
    }

    public function test_menu_do_gerente_mostra_tudo(): void
    {
        $this->actingAs($this->gerente())->get('/colecao')
            ->assertSee('Tabelas auxiliares')
            ->assertSee(route('usuario.index'));
    }

    public function test_formularios_exigem_token_csrf(): void
    {
        // O Laravel pula o CSRF quando o ambiente é "testing"; aqui fingimos
        // estar fora dos testes para conferir que a proteção está ativa.
        $usuario = User::factory()->create(['password' => 'segredo123']);
        $this->app['env'] = 'local';

        $this->post('/login', ['email' => $usuario->email, 'password' => 'segredo123'])
            ->assertStatus(419);

        $this->assertGuest();
    }
}
