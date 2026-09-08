<?php

namespace App\Observers;

use App\Models\Controle;
use App\Models\ControleHistorico;

class ControleObserver
{
    private array $ignorar = ['id'];

    public function created(Controle $controle): void
    {
        ControleHistorico::create([
            'controle_id'    => $controle->id,
            'acao'           => 'criado',
            'campo'          => null,
            'valor_anterior' => null,
            'valor_novo'     => $this->resumo($controle),
            'usuario'        => $this->usuarioAtual(),
        ]);
    }

    public function updated(Controle $controle): void
    {
        foreach ($controle->getChanges() as $campo => $valorNovo) {
            if (in_array($campo, $this->ignorar, true)) {
                continue;
            }

            ControleHistorico::create([
                'controle_id'    => $controle->id,
                'acao'           => 'atualizado',
                'campo'          => $campo,
                'valor_anterior' => $controle->getOriginal($campo),
                'valor_novo'     => $valorNovo,
                'usuario'        => $this->usuarioAtual(),
            ]);
        }
    }

    public function deleted(Controle $controle): void
    {
        ControleHistorico::create([
            'controle_id'    => $controle->id,
            'acao'           => 'excluido',
            'campo'          => null,
            'valor_anterior' => $this->resumo($controle),
            'valor_novo'     => null,
            'usuario'        => $this->usuarioAtual(),
        ]);
    }

    private function resumo(Controle $controle): string
    {
        return "nome: {$controle->nome}, quantidade: {$controle->quantidade}";
    }

    private function usuarioAtual(): string
    {
        // Sem login ainda -> "Sistema". Depois de ter autenticação, troque por:
        // return auth()->check() ? auth()->user()->name : 'Sistema';
        return 'Sistema';
    }
}