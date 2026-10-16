<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Antes: o histórico tinha chave estrangeira com onDelete('cascade').
 * Ao excluir um item, o banco apagava o histórico dele junto e, em seguida,
 * o Observer tentava gravar a linha "excluido" apontando para um item que
 * não existia mais -> erro 500 (e o item já tinha sido apagado).
 *
 * Agora: o histórico guarda só o id do item (com índice, sem chave estrangeira),
 * então ele sobrevive à exclusão e registra quem excluiu e quando.
 *
 * As tabelas são recriadas (copiando os dados) porque o SQLite, usado nos
 * testes, não consegue remover uma chave estrangeira com ALTER TABLE.
 */
return new class extends Migration
{
    private array $tabelas = [
        'console_historico'   => 'console_id',
        'controle_historico'  => 'controle_id',
        'jogo_historico'      => 'jogo_id',
        'acessorio_historico' => 'acessorio_id',
    ];

    private array $colunas = ['id', 'acao', 'campo', 'valor_anterior', 'valor_novo', 'usuario', 'created_at'];

    public function up(): void
    {
        foreach ($this->tabelas as $tabela => $fk) {
            $this->recriar($tabela, $fk, function (Blueprint $table) use ($fk) {
                $table->unsignedBigInteger($fk)->index();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabelas as $tabela => $fk) {
            $pai = str_replace('_historico', '', $tabela);

            // A chave estrangeira não aceita histórico de item que já foi excluído
            DB::table($tabela)->whereNotIn($fk, DB::table($pai)->select('id'))->delete();

            $this->recriar($tabela, $fk, function (Blueprint $table) use ($fk, $pai) {
                $table->foreignId($fk)->constrained($pai)->onDelete('cascade');
            });
        }
    }

    private function recriar(string $tabela, string $fk, callable $colunaDoItem): void
    {
        $antiga = "{$tabela}_antiga";
        Schema::rename($tabela, $antiga);

        Schema::create($tabela, function (Blueprint $table) use ($colunaDoItem) {
            $table->id();
            $colunaDoItem($table);
            $table->string('acao', 20);
            $table->string('campo', 50)->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_novo')->nullable();
            $table->string('usuario')->nullable()->default('Sistema');
            $table->timestamp('created_at')->useCurrent();
        });

        $colunas = array_merge($this->colunas, [$fk]);
        DB::table($tabela)->insertUsing($colunas, DB::table($antiga)->select($colunas));

        Schema::drop($antiga);
    }
};
