<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controle_historico', function (Blueprint $table) {
            $table->id();

            // Mesma lógica da tabela console_historico, só que ligada
            // ao controle. onDelete('cascade') apaga o histórico junto
            // se o controle for excluído de vez do banco.
            $table->foreignId('controle_id')->constrained('controle')->onDelete('cascade');

            // 'criado', 'atualizado' ou 'excluido'.
            $table->string('acao', 20);

            // Nome do campo alterado (ex: "quantidade", "cores").
            // Null quando a ação é 'criado' ou 'excluido'.
            $table->string('campo', 50)->nullable();

            // Valor antes/depois, sempre como texto.
            $table->text('valor_anterior')->nullable();
            $table->text('valor_novo')->nullable();

            // Sem login ainda: texto livre com padrão "Sistema".
            $table->string('usuario')->nullable()->default('Sistema');

            // Só precisamos do "quando" — log não é editado depois.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controle_historico');
    }
};