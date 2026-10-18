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
                // Preço em reais (até 99.999.999,99). Opcional: os itens já
                // cadastrados ficam sem valor até alguém preencher.
                $table->decimal('valor', 10, 2)->nullable()->after('quantidade');
            });
        }

        Schema::table('console', function (Blueprint $table) {
            // Em GB (ex: 825, 1000). Opcional: console retrô não tem armazenamento.
            $table->unsignedInteger('armazenamento')->nullable()->after('valor');
        });
    }

    public function down(): void
    {
        Schema::table('console', function (Blueprint $table) {
            $table->dropColumn('armazenamento');
        });

        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropColumn('valor');
            });
        }
    }
};
