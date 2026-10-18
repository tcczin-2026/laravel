<?php

namespace App\Http\Controllers;

use App\Models\Acessorio;
use App\Models\AcessorioHistorico;
use App\Models\Console;
use App\Models\ConsoleHistorico;
use App\Models\Controle;
use App\Models\ControleHistorico;
use App\Models\Jogo;
use App\Models\JogoHistorico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Reverte alterações usando o histórico.
 *
 * Exemplo: 14:30 quantidade 15 -> 17 (pessoa A)
 *          14:35 quantidade 17 -> 19 (pessoa B)  <- errado, o certo era 17
 *
 * - desfazer(linha das 14:35): volta só aquele campo para o valor anterior (17).
 * - restaurar(linha das 14:30): volta o item ao estado logo depois das 14:30,
 *   desfazendo tudo que veio depois (de todos os campos).
 *
 * A reversão também passa pelo Observer, então fica gravada no histórico
 * como 'revertido' (nada é apagado do histórico).
 */
class HistoricoController extends Controller
{
    /** Campos que não podem ser revertidos (a imagem antiga é apagada do disco ao trocar) */
    private array $naoReversiveis = ['imagem'];

    /** Ações do histórico que representam mudança de um campo */
    private array $acoesDeCampo = ['atualizado', 'revertido'];

    public static function tipos(): array
    {
        return [
            'console'   => ['model' => Console::class,   'historico' => ConsoleHistorico::class,   'fk' => 'console_id'],
            'controle'  => ['model' => Controle::class,  'historico' => ControleHistorico::class,  'fk' => 'controle_id'],
            'jogo'      => ['model' => Jogo::class,      'historico' => JogoHistorico::class,      'fk' => 'jogo_id'],
            'acessorio' => ['model' => Acessorio::class, 'historico' => AcessorioHistorico::class, 'fk' => 'acessorio_id'],
        ];
    }

    /** Desfaz uma única alteração de campo */
    public function desfazer(string $tipo, int $id)
    {
        $config = $this->config($tipo);
        $linha  = $config['historico']::findOrFail($id);

        if (! in_array($linha->acao, $this->acoesDeCampo, true) || ! $linha->campo) {
            return back()->with('error', 'Só é possível desfazer alterações de um campo.');
        }

        if (in_array($linha->campo, $this->naoReversiveis, true)) {
            return back()->with('error', "O campo \"{$linha->campo}\" não pode ser revertido.");
        }

        $registro = $config['model']::findOrFail($linha->{$config['fk']});

        // Se alguém mudou o campo depois dessa alteração, desfazer apagaria
        // o trabalho dessa pessoa. Nesse caso, o certo é usar "Restaurar até aqui".
        if (! $this->mesmoValor($registro->{$linha->campo}, $linha->valor_novo)) {
            return back()->with('error',
                "O campo \"{$linha->campo}\" foi alterado de novo depois disso (hoje vale \""
                . ($registro->{$linha->campo} ?? 'vazio') . "\"). "
                . 'Use "Restaurar até aqui" na linha anterior para voltar àquele ponto.');
        }

        return $this->salvar($registro, [$linha->campo => $linha->valor_anterior]);
    }

    /** Volta o item ao estado em que ficou logo após esta linha do histórico */
    public function restaurar(string $tipo, int $id)
    {
        $config = $this->config($tipo);
        $linha  = $config['historico']::findOrFail($id);

        if ($linha->acao === 'excluido') {
            return back()->with('error', 'Não é possível restaurar a partir de uma exclusão.');
        }

        $registro = $config['model']::findOrFail($linha->{$config['fk']});

        // Todas as alterações de campo feitas DEPOIS desta linha, da mais nova
        // pra mais antiga. O valor que vale para cada campo é o "valor_anterior"
        // da alteração mais antiga dele (a última sobrescrita no loop).
        $posteriores = $config['historico']::where($config['fk'], $registro->id)
            ->where('id', '>', $linha->id)
            ->whereIn('acao', $this->acoesDeCampo)
            ->whereNotNull('campo')
            ->orderByDesc('id')
            ->get();

        $valores = [];
        foreach ($posteriores as $alteracao) {
            if (in_array($alteracao->campo, $this->naoReversiveis, true)) {
                continue;
            }
            $valores[$alteracao->campo] = $alteracao->valor_anterior;
        }

        return $this->salvar($registro, $valores);
    }

    private function salvar(Model $registro, array $valores)
    {
        // Ignora campos que já estão com o valor desejado
        $valores = array_filter($valores, fn ($valor, $campo) =>
            ! $this->mesmoValor($registro->{$campo}, $valor), ARRAY_FILTER_USE_BOTH);

        if (empty($valores)) {
            return back()->with('success', 'Nada para reverter: o item já está nesse estado.');
        }

        try {
            DB::transaction(function () use ($registro, $valores) {
                foreach ($valores as $campo => $valor) {
                    $registro->{$campo} = $valor;
                }
                $registro->acaoHistorico = 'revertido'; // o Observer grava com essa ação
                $registro->save();
            });
        } catch (QueryException $e) {
            // Ex: o valor antigo aponta para uma cor/plataforma que já foi excluída
            return back()->with('error', 'Não foi possível reverter: algum valor antigo não existe mais no banco.');
        }

        $campos = implode(', ', array_keys($valores));

        return back()->with('success', "Alteração revertida com sucesso ({$campos}).");
    }

    /** Compara como texto, porque o histórico guarda tudo como texto */
    private function mesmoValor($atual, $historico): bool
    {
        // Preço: "1299.9" e "1299.90" são o mesmo valor
        if (is_numeric($atual) && is_numeric($historico)) {
            return (float) $atual === (float) $historico;
        }

        return (string) $atual === (string) $historico && is_null($atual) === is_null($historico);
    }

    private function config(string $tipo): array
    {
        return self::tipos()[$tipo] ?? abort(404);
    }
}
