<?php

namespace App\Http\Controllers;

use App\Models\Console;
use App\Models\Cor;
use App\Models\Desbloqueado;
use App\Models\Digital;
use App\Models\EdicaoEspecial;
use App\Models\plataforma;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ConsoleController extends Controller
{
    /** READ - lista com filtros */
    public function index(Request $request)
    {
        $query = Console::with([
            'plataforma', 'usado', 'digital', 'cor', 'retro', 'desbloqueado', 'edicaoEspecial',
        ]);

        // Filtro por nome (busca parcial)
        $query->when($request->nome, function ($q, $nome) {
            $q->where('nome', 'like', "%{$nome}%");
        });

        // Filtro por plataforma
        $query->when($request->plataformaConsole, function ($q, $plataforma) {
            $q->where('plataformaConsole', $plataforma);
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

        $consoles = $query->orderBy('id')->get();

        return view('console.index', $this->listas() + [
            'consoles' => $consoles,
        ]);
    }

    /** READ - detalhe */
    public function show(int $id)
    {
        $console = Console::with([
            'plataforma', 'usado', 'digital', 'cor', 'retro', 'desbloqueado', 'edicaoEspecial',
            'controles', 'jogos', 'acessorios',
        ])->findOrFail($id);

        return view('console.show', ['console' => $console]);
    }

    /** CREATE - formulario */
    public function create()
    {
        return view('console.form', $this->listas() + ['console' => null]);
    }

    /** CREATE - grava */
    public function store(Request $request)
    {
        Console::create($this->validar($request));

        return redirect()
            ->route('console.index')
            ->with('success', 'Console cadastrado com sucesso!');
    }

    /** UPDATE - formulario */
    public function edit(int $id)
    {
        return view('console.form', $this->listas() + ['console' => Console::findOrFail($id)]);
    }

    /** UPDATE - grava */
    public function update(Request $request, int $id)
    {
        Console::findOrFail($id)->update($this->validar($request));

        return redirect()
            ->route('console.index')
            ->with('success', 'Console atualizado com sucesso!');
    }

    /** DELETE */
    public function destroy(int $id)
    {
        try {
            Console::findOrFail($id)->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('console.index')
                ->with('error', 'Nao e possivel excluir: existem controles, jogos ou acessorios ligados a este console.');
        }

        return redirect()
            ->route('console.index')
            ->with('success', 'Console excluido com sucesso!');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nome'         => 'required|string|max:100',
            'plataformaConsole' => 'required|exists:plataforma,id',
            'quantidade'   => 'required|integer|min:0',
            'estado'       => 'required|exists:usado,id',
            'leitor'       => 'required|exists:digital,id',
            'cores'        => 'required|exists:cor,id',
            'vintage'      => 'required|exists:retro,id',
            'aberto'       => 'required|exists:desbloqueado,id',
            'colecionador' => 'required|exists:edicao_especial,id',
        ]);
    }

    /** Listas que alimentam os selects do formulario */
    private function listas(): array
    {
        return [
            'plataformas'   => plataforma::orderBy('nome')->get(),
            'usados'        => Usado::orderBy('nome')->get(),
            'digitais'      => Digital::orderBy('nome')->get(),
            'cores'         => Cor::orderBy('nome')->get(),
            'retros'        => Retro::orderBy('nome')->get(),
            'desbloqueados' => Desbloqueado::orderBy('nome')->get(),
            'edicoes'       => EdicaoEspecial::orderBy('nome')->get(),
        ];
    }
}