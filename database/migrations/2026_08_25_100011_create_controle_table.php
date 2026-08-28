<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controle', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->foreignId('plataformaControle')->constrained('plataforma');
            $table->integer('quantidade')->default(0);
            $table->foreignId('cores')->constrained('cor');
            $table->foreignId('vintage')->constrained('retro');
            $table->foreignId('estado')->constrained('usado');
            $table->foreignId('colecionador')->constrained('edicao_especial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controle');
    }
};
