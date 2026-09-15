<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acessorio', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->foreignId('Plataforma')->constrained('plataforma');
            $table->integer('quantidade')->default(0);
            $table->foreignId('estado')->constrained('usado');
            $table->foreignId('vintage')->constrained('retro');
            $table->foreignId('cores')->constrained('cor');
            $table->foreignId('colecionador')->constrained('edicao_especial');
        });
    }

     /**
     * Histórico de alterações deste acessorio, do mais recente pro mais
     * antigo (->latest() ordena por created_at desc). É alimentado
     * automaticamente pelo acessorioObserver a cada create/update/delete
     * — você nunca cria um registro de histórico na mão.
     */
    public function historico()
    {
        return $this->hasMany(AcessorioHistorico::class, 'acessorio_id')->latest();
    }


    public function down(): void
    {
        Schema::dropIfExists('acessorio');
    }
};
