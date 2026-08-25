<?php

namespace App\Http\Controllers;

use App\Models\Console;
use App\Models\Controle;
use App\Models\Cor;
use App\Models\EdicaoEspecial;
use App\Models\Marca;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Http\Request;

class ControleController extends Controller
{
    /** READ - lista */
    public function index()
    {
        $controles = Controle::with([
            'marca', 'cor', 'retro', 'console', 'usado', 'edicaoEspecial',
        ])->orderBy('nome')->get();

        return view('controle.index', ['controles' => $controles]);
    }

    /** READ - detalhe */
    public function show(int $id)
    {
        $controle = Controle::with([
            'marca', 'cor', 'retro', 'console', 'usado', 'edicaoEspecial',
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
            'MarcaControle' => 'required|exists:marca,id',
            'quantidade'    => 'required|integer|min:0',
            'cores'         => 'required|exists:cor,id',
            'vintage'       => 'required|exists:retro,id',
            'dispositivo'   => 'required|exists:console,id',
            'estado'        => 'required|exists:usado,id',
            'colecionador'  => 'required|exists:edicao_especial,id',
        ]);
    }

    private function listas(): array
    {
        return [
            'marcas'   => Marca::orderBy('nome')->get(),
            'cores'    => Cor::orderBy('nome')->get(),
            'retros'   => Retro::orderBy('nome')->get(),
            'consoles' => Console::orderBy('nome')->get(),
            'usados'   => Usado::orderBy('nome')->get(),
            'edicoes'  => EdicaoEspecial::orderBy('nome')->get(),
        ];
    }
}
