<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('console_historico', function (Blueprint $table) {
            $table->id();

            // Liga cada linha do histórico ao console que sofreu a alteração.
            // onDelete('cascade'): se o console for apagado de vez do banco,
            // o histórico dele some junto (evita registro "órfão").
            // Obs: assumindo que a tabela se chama "console", seguindo o
            // mesmo padrão sem plural das outras tabelas do projeto.
            $table->foreignId('console_id')->constrained('console')->onDelete('cascade');

            // Tipo de ação: 'criado', 'atualizado' ou 'excluido'.
            $table->string('acao', 20);

            // Nome do campo alterado (ex: "quantidade", "estado").
            // Fica null quando a ação é 'criado' ou 'excluido', pois nesses
            // casos guardamos um resumo do registro inteiro, não campo a campo.
            $table->string('campo', 50)->nullable();

            // Valor antes e depois da mudança, sempre como texto: um único
            // histórico precisa guardar campos de tipos diferentes
            // (quantidade é número, estado é FK, etc), então texto resolve todos.
            $table->text('valor_anterior')->nullable();
            $table->text('valor_novo')->nullable();

            // Como ainda não existe login no sistema, guardamos só um texto
            // livre (com padrão "Sistema"). Quando implementar autenticação,
            // basta passar o nome do usuário logado nesse mesmo campo, ou
            // trocar por usuario_id + relação com a tabela de usuários.
            $table->string('usuario')->nullable()->default('Sistema');

            // Um log não é editado depois de criado, então só faz sentido
            // guardar QUANDO ele aconteceu (não precisamos de updated_at).
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('console_historico');
    }
};