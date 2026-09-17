<?php

namespace App\Observers;

use App\Models\acessorio;
use App\Models\acessorioHistorico;

class acessorioObserver
{
    /**
     * Campos que nunca entram no histórico, mesmo que "mudem".
     * 'id' está aqui só por segurança (na prática não muda nunca).
     */
    private array $ignorar = ['id'];

    /**
     * Disparado automaticamente pelo Eloquent logo após um
     * acessorio::create(...). Grava 1 linha com um resumo do registro.
     */
    public function created(acessorio $acessorio): void
    {
        acessorioHistorico::create([
            'acessorio_id'     => $acessorio->id,
            'acao'           => 'criado',
            'campo'          => null,
            'valor_anterior' => null,
            'valor_novo'     => $this->resumo($acessorio),
            'usuario'        => $this->usuarioAtual(),
        ]);
    }

    /**
     * Disparado logo após um ->update(...) ou ->save() que alterou
     * algo. getChanges() traz só os campos que mudaram NESSE save;
     * getOriginal($campo) traz o valor de cada um antes da mudança.
     * Resultado: 1 linha de histórico POR CAMPO alterado.
     */
    public function updated(acessorio $acessorio): void
    {
        foreach ($acessorio->getChanges() as $campo => $valorNovo) {
            if (in_array($campo, $this->ignorar, true)) {
                continue;
            }

            AcessorioHistorico::create([
                'acessorio_id'     => $acessorio->id,
                'acao'           => 'atualizado',
                'campo'          => $campo,
                'valor_anterior' => $acessorio->getOriginal($campo),
                'valor_novo'     => $valorNovo,
                'usuario'        => $this->usuarioAtual(),
            ]);
        }
    }

    /**
     * Disparado logo após um ->delete(). Como o registro está prestes
     * a sumir, guardamos um resumo dele em valor_anterior.
     */
    public function deleted(acessorio $acessorio): void
    {
        AcessorioHistorico::create([
            'acessorio_id'     => $acessorio->id,
            'acao'           => 'excluido',
            'campo'          => null,
            'valor_anterior' => $this->resumo($acessorio),
            'valor_novo'     => null,
            'usuario'        => $this->usuarioAtual(),
        ]);
    }

    private function resumo(acessorio $acessorio): string
    {
        return "nome: {$acessorio->nome}, quantidade: {$acessorio->quantidade}";
    }

    private function usuarioAtual(): string
    {
        // Ainda não existe login no projeto, então tudo fica como "Sistema".
        // Quando implementar autenticação, troque a linha abaixo por:
        // return auth()->check() ? auth()->user()->name : 'Sistema';
        return 'Sistema';
    }


    public function boot(): void
    {
        Acessorio::observe(AcessorioObserver::class);
        // Console::observe(ConsoleObserver::class); ← provavelmente já está aqui
    }
}