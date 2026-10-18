<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Deixa o histórico legível nas telas: o banco guarda ids (ex: cores 1 -> 3)
 * e aqui eles viram nomes (ex: cor azul -> branco).
 *
 * Só afeta a exibição: valor_anterior / valor_novo continuam com o id,
 * que é o que a reversão (HistoricoController) usa.
 *
 * Uso nas views: $item->campo_rotulo, $item->de_texto, $item->para_texto
 */
trait FormataHistorico
{
    /** campo do item => [rótulo na tela, tabela auxiliar de onde vem o nome] */
    private static array $camposConhecidos = [
        'nome'               => ['nome', null],
        'quantidade'         => ['quantidade', null],
        'valor'              => ['valor', null],
        'armazenamento'      => ['armazenamento (GB)', null],
        'imagem'             => ['imagem', null],
        'plataformaconsole'  => ['plataforma', 'plataforma'],
        'plataformacontrole' => ['plataforma', 'plataforma'],
        'plataforma'         => ['plataforma', 'plataforma'],
        'estado'             => ['estado', 'usado'],
        'leitor'             => ['leitor', 'digital'],
        'cores'              => ['cor', 'cor'],
        'vintage'            => ['retrô', 'retro'],
        'aberto'             => ['desbloqueio', 'desbloqueado'],
        'colecionador'       => ['edição especial', 'edicao_especial'],
        'dispositivo'        => ['console', 'console'],
    ];

    public function getCampoRotuloAttribute(): ?string
    {
        if (! $this->campo) {
            return null;
        }

        return self::$camposConhecidos[strtolower($this->campo)][0] ?? $this->campo;
    }

    public function getDeTextoAttribute(): ?string
    {
        return $this->traduzir($this->valor_anterior, 'foto anterior');
    }

    public function getParaTextoAttribute(): ?string
    {
        return $this->traduzir($this->valor_novo, 'nova foto');
    }

    private function traduzir($valor, string $textoFoto): ?string
    {
        $tabela = self::$camposConhecidos[strtolower((string) $this->campo)][1] ?? null;

        // Foto: o caminho do arquivo (consoles/0ZWz...jpg) não diz nada pra quem lê
        if (strtolower((string) $this->campo) === 'imagem') {
            return $valor ? $textoFoto : 'sem foto';
        }

        if (strtolower((string) $this->campo) === 'valor' && is_numeric($valor)) {
            return 'R$ ' . number_format((float) $valor, 2, ',', '.');
        }

        if ($valor === null || $valor === '' || ! $tabela) {
            return $valor === null ? null : (string) $valor;
        }

        // 1 consulta por tabela por requisição (cache "array" = só em memória)
        $nomes = cache()->store('array')->rememberForever(
            "historico_nomes_{$tabela}",
            fn () => DB::table($tabela)->pluck('nome', 'id')->all()
        );

        // Se o registro auxiliar foi excluído depois, mostra o id mesmo
        return $nomes[$valor] ?? "#{$valor} (excluído)";
    }
}
