<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tabelas = ['console', 'controle', 'jogo', 'acessorio'];

    public function up(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                // EAN-8, UPC-A (12), EAN-13 ou GTIN-14, só dígitos. Opcional: os itens
                // já cadastrados ficam sem código até alguém preencher. Não é único porque
                // a coleção tem mais de uma linha do mesmo produto (ex: PS5 novo e usado).
                $table->string('codigo_barras', 14)->nullable()->index()->after('nome');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropIndex(['codigo_barras']);
                $table->dropColumn('codigo_barras');
            });
        }
    }
};
