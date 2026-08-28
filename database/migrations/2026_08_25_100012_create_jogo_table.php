<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jogo', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->foreignId('Plataforma')->constrained('plataforma');
            $table->integer('quantidade')->default(0);
            $table->foreignId('estado')->constrained('usado');
            $table->foreignId('vintage')->constrained('retro');
            $table->foreignId('colecionador')->constrained('edicao_especial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jogo');
    }
};
