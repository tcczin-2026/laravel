<?php

namespace App\Observers;

use App\Models\Console;
use App\Models\ConsoleHistorico;

class ConsoleObserver
{
    /**
     * Campos que nunca entram no histórico, mesmo que "mudem".
     * 'id' está aqui só por segurança (na prática não muda nunca).
     */
    private array $ignorar = ['id'];

    /**
     * Disparado automaticamente pelo Eloquent logo após um
     * Console::create(...). Grava 1 linha com um resumo do registro.
     */
    public function created(Console $console): void
    {
        ConsoleHistorico::create([
            'console_id'     => $console->id,
            'acao'           => 'criado',
            'campo'          => null,
            'valor_anterior' => null,
            'valor_novo'     => $this->resumo($console),
            'usuario'        => $this->usuarioAtual(),
        ]);
    }

    /**
     * Disparado logo após um ->update(...) ou ->save() que alterou
     * algo. getChanges() traz só os campos que mudaram NESSE save;
     * getOriginal($campo) traz o valor de cada um antes da mudança.
     * Resultado: 1 linha de histórico POR CAMPO alterado.
     */
    public function updated(Console $console): void
    {
        foreach ($console->getChanges() as $campo => $valorNovo) {
            if (in_array($campo, $this->ignorar, true)) {
                continue;
            }

            ConsoleHistorico::create([
                'console_id'     => $console->id,
                'acao'           => 'atualizado',
                'campo'          => $campo,
                'valor_anterior' => $console->getOriginal($campo),
                'valor_novo'     => $valorNovo,
                'usuario'        => $this->usuarioAtual(),
            ]);
        }
    }

    /**
     * Disparado logo após um ->delete(). Como o registro está prestes
     * a sumir, guardamos um resumo dele em valor_anterior.
     */
    public function deleted(Console $console): void
    {
        ConsoleHistorico::create([
            'console_id'     => $console->id,
            'acao'           => 'excluido',
            'campo'          => null,
            'valor_anterior' => $this->resumo($console),
            'valor_novo'     => null,
            'usuario'        => $this->usuarioAtual(),
        ]);
    }

    private function resumo(Console $console): string
    {
        return "nome: {$console->nome}, quantidade: {$console->quantidade}";
    }

    private function usuarioAtual(): string
    {
        // Ainda não existe login no projeto, então tudo fica como "Sistema".
        // Quando implementar autenticação, troque a linha abaixo por:
        // return auth()->check() ? auth()->user()->name : 'Sistema';
        return 'Sistema';
    }
}