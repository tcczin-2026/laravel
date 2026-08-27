<?php

namespace App\Http\Controllers;

use App\Models\Cor;
use App\Models\Desbloqueado;
use App\Models\Digital;
use App\Models\EdicaoEspecial;
use App\Models\plataforma;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

/**
 * CRUD das sete tabelas auxiliares do diagrama (plataforma, cor, retro, usado,
 * digital, desbloqueado e edicao_especial). Todas tem a mesma estrutura
 * (id + nome), entao um unico controller atende as sete, escolhendo o model
 * pelo segmento {tipo} da rota.
 */
class AuxiliarController extends Controller
{
    private const TIPOS = [
        'plataforma'           => ['model' => plataforma::class,          'titulo' => 'plataformas'],
        'cor'             => ['model' => Cor::class,            'titulo' => 'Cores'],
        'retro'           => ['model' => Retro::class,          'titulo' => 'Retro / Vintage'],
        'usado'           => ['model' => Usado::class,          'titulo' => 'Estado (Usado)'],
        'digital'         => ['model' => Digital::class,        'titulo' => 'Leitor (Digital)'],
        'desbloqueado'    => ['model' => Desbloqueado::class,   'titulo' => 'Desbloqueado'],
        'edicao_especial' => ['model' => EdicaoEspecial::class, 'titulo' => 'Edicao Especial'],
    ];

    public static function tipos(): array
    {
        return self::TIPOS;
    }

    /** READ - lista todos os registros do tipo */
    public function index(string $tipo)
    {
        $config = $this->config($tipo);
        $model = $config['model'];

        return view('auxiliar.index', [
            'tipo'      => $tipo,
            'titulo'    => $config['titulo'],
            'registros' => $model::orderBy('id')->get(),
        ]);
    }

    /** CREATE - formulario */
    public function create(string $tipo)
    {
        $config = $this->config($tipo);

        return view('auxiliar.form', [
            'tipo'     => $tipo,
            'titulo'   => $config['titulo'],
            'registro' => null,
        ]);
    }

    /** CREATE - grava */
    public function store(Request $request, string $tipo)
    {
        $config = $this->config($tipo);
        $dados = $request->validate([
            'nome' => 'required|string|max:20',
        ]);

        $config['model']::create($dados);

        return redirect()
            ->route('auxiliar.index', $tipo)
            ->with('success', 'Registro cadastrado com sucesso!');
    }

    /** UPDATE - formulario */
    public function edit(string $tipo, int $id)
    {
        $config = $this->config($tipo);

        return view('auxiliar.form', [
            'tipo'     => $tipo,
            'titulo'   => $config['titulo'],
            'registro' => $config['model']::findOrFail($id),
        ]);
    }

    /** UPDATE - grava */
    public function update(Request $request, string $tipo, int $id)
    {
        $config = $this->config($tipo);
        $dados = $request->validate([
            'nome' => 'required|string|max:20',
        ]);

        $config['model']::findOrFail($id)->update($dados);

        return redirect()
            ->route('auxiliar.index', $tipo)
            ->with('success', 'Registro atualizado com sucesso!');
    }

    /** DELETE */
    public function destroy(string $tipo, int $id)
    {
        $config = $this->config($tipo);

        try {
            $config['model']::findOrFail($id)->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('auxiliar.index', $tipo)
                ->with('error', 'Nao e possivel excluir: o registro esta em uso.');
        }

        return redirect()
            ->route('auxiliar.index', $tipo)
            ->with('success', 'Registro excluido com sucesso!');
    }

    /** Resolve o tipo da rota ou devolve 404 */
    private function config(string $tipo): array
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404, "Tabela auxiliar '{$tipo}' nao existe.");

        return self::TIPOS[$tipo];
    }
}
