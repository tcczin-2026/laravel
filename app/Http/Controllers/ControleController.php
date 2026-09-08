<?php

namespace App\Http\Controllers;

use App\Models\Controle;
use App\Models\Cor;
use App\Models\EdicaoEspecial;
use App\Models\plataforma;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Http\Request;

class ControleController extends Controller
{

    /** READ - lista com filtros */
    public function index(Request $request)
    {
        $query = controle::with([
            'plataforma', 'usado', 'cor', 'retro', 'edicaoEspecial',
        ]);

        // Filtro por nome (busca parcial)
        $query->when($request->nome, function ($q, $nome) {
            $q->where('nome', 'like', "%{$nome}%");
        });

        // Filtro por plataforma
        $query->when($request->plataformacontrole, function ($q, $plataforma) {
            $q->where('plataformacontrole', $plataforma);
        });

        // Filtro por estado (usado/novo)
        $query->when($request->estado, function ($q, $estado) {
            $q->where('estado', $estado);
        });

        // Filtro por cor
        $query->when($request->cores, function ($q, $cor) {
            $q->where('cores', $cor);
        });

        // Filtro por faixa de quantidade em estoque
        $query->when($request->quantidade_min, function ($q, $min) {
            $q->where('quantidade', '>=', $min);
        });

        $controles = $query->orderBy('id')->get();

        return view('controle.index', $this->listas() + [
            'controles' => $controles,
        ]);
    }

    /**
     * READ - detalhe de um único controle.
     *
     * Antes esse método buscava TODOS os controles e devolvia pra view
     * "controle.index" — não batia com a view "controle.show" que você já
     * tem, que espera um único $controle. Corrigido pra usar findOrFail()
     * e já carregar 'historico' junto, pra o bloco de histórico funcionar
     * sem precisar de uma segunda consulta na view.
     */
    public function show(int $id)
    {
        $controle = Controle::with([
            'plataforma', 'cor', 'retro', 'usado', 'edicaoEspecial', 'historico',
        ])->findOrFail($id);
 
        return view('controle.show', ['controle' => $controle]);
    }

    /** CREATE - formulario */
    public function create()
    {
        return view('controle.form', $this->listas() + ['controle' => null]);
    }

    /** CREATE - grava */
    public function store(Request $request)
    {
        Controle::create($this->validar($request));

        return redirect()
            ->route('controle.index')
            ->with('success', 'Controle cadastrado com sucesso!');
    }

    /** UPDATE - formulario */
    public function edit(int $id)
    {
        return view('controle.form', $this->listas() + ['controle' => Controle::findOrFail($id)]);
    }

    /** UPDATE - grava */
    public function update(Request $request, int $id)
    {
        Controle::findOrFail($id)->update($this->validar($request));

        return redirect()
            ->route('controle.index')
            ->with('success', 'Controle atualizado com sucesso!');
    }

    /** DELETE */
    public function destroy(int $id)
    {
        Controle::findOrFail($id)->delete();

        return redirect()
            ->route('controle.index')
            ->with('success', 'Controle excluido com sucesso!');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nome'          => 'required|string|max:100',
            'plataformaControle' => 'required|exists:plataforma,id',
            'quantidade'    => 'required|integer|min:0',
            'cores'         => 'required|exists:cor,id',
            'vintage'       => 'required|exists:retro,id',
            'estado'        => 'required|exists:usado,id',
            'colecionador'  => 'required|exists:edicao_especial,id',
        ]);
    }

    private function listas(): array
    {
        return [
            'plataformas'   => plataforma::orderBy('nome')->get(),
            'cores'    => Cor::orderBy('nome')->get(),
            'retros'   => Retro::orderBy('nome')->get(),
            'usados'   => Usado::orderBy('nome')->get(),
            'edicoes'  => EdicaoEspecial::orderBy('nome')->get(),
        ];
    }
}
