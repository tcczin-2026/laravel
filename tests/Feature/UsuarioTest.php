<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Cadastro de usuários (só gerente) */
class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    private function dados(array $extra = []): array
    {
        return array_merge([
            'name' => 'Maria', 'email' => 'maria@teste.com', 'perfil' => 'funcionario',
            'password' => 'segredo1', 'password_confirmation' => 'segredo1',
        ], $extra);
    }

    public function test_gerente_cadastra_usuario_com_senha_criptografada(): void
    {
        $this->actingAs($this->gerente())->post('/usuario', $this->dados())
            ->assertRedirect(route('usuario.index'));

        $maria = User::where('email', 'maria@teste.com')->first();
        $this->assertSame('funcionario', $maria->perfil);
        $this->assertNotSame('segredo1', $maria->password);
        $this->assertTrue(Hash::check('segredo1', $maria->password));
    }

    public function test_usuario_cadastrado_consegue_fazer_login(): void
    {
        $this->actingAs($this->gerente())->post('/usuario', $this->dados());
        $this->post('/logout');

        $this->post('/login', ['email' => 'maria@teste.com', 'password' => 'segredo1']);

        $this->assertAuthenticated();
    }

    public function test_email_duplicado_e_recusado(): void
    {
        User::factory()->create(['email' => 'maria@teste.com']);

        $this->actingAs($this->gerente())->post('/usuario', $this->dados())
            ->assertSessionHasErrors('email');
    }

    public function test_senha_curta_ou_sem_confirmacao_e_recusada(): void
    {
        $gerente = $this->gerente();

        $this->actingAs($gerente)->post('/usuario', $this->dados(['password' => '123', 'password_confirmation' => '123']))
            ->assertSessionHasErrors('password');
        $this->actingAs($gerente)->post('/usuario', $this->dados(['password_confirmation' => 'diferente']))
            ->assertSessionHasErrors('password');
    }

    public function test_perfil_invalido_e_recusado(): void
    {
        $this->actingAs($this->gerente())->post('/usuario', $this->dados(['perfil' => 'admin']))
            ->assertSessionHasErrors('perfil');
    }

    public function test_editar_sem_senha_mantem_a_senha_atual(): void
    {
        $maria = User::factory()->create(['password' => 'antiga123']);

        $this->actingAs($this->gerente())->put("/usuario/{$maria->id}", [
            'name' => 'Maria Silva', 'email' => $maria->email, 'perfil' => 'funcionario',
            'password' => '', 'password_confirmation' => '',
        ])->assertRedirect(route('usuario.index'));

        $maria->refresh();
        $this->assertSame('Maria Silva', $maria->name);
        $this->assertTrue(Hash::check('antiga123', $maria->password));
    }

    public function test_editar_mantendo_o_proprio_email_e_permitido(): void
    {
        $gerente = $this->gerente();

        $this->actingAs($gerente)->put("/usuario/{$gerente->id}", [
            'name' => 'Chefe', 'email' => $gerente->email, 'perfil' => 'gerente',
        ])->assertSessionHasNoErrors();
    }

    public function test_gerente_nao_exclui_a_si_mesmo(): void
    {
        $gerente = $this->gerente();

        $this->actingAs($gerente)->delete("/usuario/{$gerente->id}")->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $gerente->id]);
    }

    public function test_unico_gerente_nao_pode_ser_rebaixado(): void
    {
        $gerente = $this->gerente();
        User::where('id', '!=', $gerente->id)->delete(); // tira o gerente padrão da migration

        $this->actingAs($gerente)->put("/usuario/{$gerente->id}", [
            'name' => $gerente->name, 'email' => $gerente->email, 'perfil' => 'funcionario',
        ])->assertSessionHas('error');

        $this->assertSame('gerente', $gerente->refresh()->perfil);
    }

    public function test_gerente_exclui_outro_usuario(): void
    {
        $maria = $this->funcionario();

        $this->actingAs($this->gerente())->delete("/usuario/{$maria->id}")->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $maria->id]);
    }

    public function test_com_dois_gerentes_um_pode_rebaixar_o_outro(): void
    {
        $outro = $this->gerente();

        $this->actingAs($this->gerente())->put("/usuario/{$outro->id}", [
            'name' => $outro->name, 'email' => $outro->email, 'perfil' => 'funcionario',
        ])->assertSessionHas('success');

        $this->assertSame('funcionario', $outro->refresh()->perfil);
    }

    public function test_lista_de_usuarios_pagina(): void
    {
        User::factory()->count(12)->create();

        $this->actingAs($this->gerente())->get('/usuario')->assertOk()->assertSee('pagination');
    }

    public function test_migration_cria_gerente_padrao_quando_nao_ha_usuarios(): void
    {
        // O RefreshDatabase roda as migrations com a tabela vazia
        $this->assertDatabaseHas('users', ['email' => 'gerente@colecao.com', 'perfil' => 'gerente']);
    }
}
