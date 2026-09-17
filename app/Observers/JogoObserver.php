<?php

namespace App\Observers;

use App\Models\Jogo;
use App\Models\JogoHistorico;

class JogoObserver
{
    private array $ignorar = ['id'];

    public function created(Jogo $jogo): void
    {
        JogoHistorico::create([
            'jogo_id'    => $jogo->id,
            'acao'           => 'criado',
            'campo'          => null,
            'valor_anterior' => null,
            'valor_novo'     => $this->resumo($jogo),
            'usuario'        => $this->usuarioAtual(),
        ]);
    }

    public function updated(Jogo $jogo): void
    {
        foreach ($jogo->getChanges() as $campo => $valorNovo) {
            if (in_array($campo, $this->ignorar, true)) {
                continue;
            }

            JogoHistorico::create([
                'jogo_id'    => $jogo->id,
                'acao'           => 'atualizado',
                'campo'          => $campo,
                'valor_anterior' => $jogo->getOriginal($campo),
                'valor_novo'     => $valorNovo,
                'usuario'        => $this->usuarioAtual(),
            ]);
        }
    }

    public function deleted(Jogo $jogo): void
    {
        JogoHistorico::create([
            'jogo_id'    => $jogo->id,
            'acao'           => 'excluido',
            'campo'          => null,
            'valor_anterior' => $this->resumo($jogo),
            'valor_novo'     => null,
            'usuario'        => $this->usuarioAtual(),
        ]);
    }

    private function resumo(Jogo $jogo): string
    {
        return "nome: {$jogo->nome}, quantidade: {$jogo->quantidade}";
    }

    private function usuarioAtual(): string
    {
        // Sem login ainda -> "Sistema". Depois de ter autenticação, troque por:
        // return auth()->check() ? auth()->user()->name : 'Sistema';
        return 'Sistema';
    }
}