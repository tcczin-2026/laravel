<?php

namespace App\Http\Controllers;

use App\Models\Acessorio;
use App\Models\plataforma;
use App\Models\Cor;
use App\Models\EdicaoEspecial;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Http\Request;

class AcessorioController extends Controller
{
    /** READ - lista */
    public function index()
    {
        $acessorios = Acessorio::with(['plataforma', 'usado', 'retro', 'cor', 'edicaoEspecial'])
            ->orderBy('nome')
            ->get();

        return view('acessorio.index', ['acessorios' => $acessorios]);
    }

    /** READ - detalhe */
    public function show(int $id)
    {
        $acessorio = Acessorio::with(['plataforma', 'usado', 'retro', 'cor', 'edicaoEspecial'])
            ->findOrFail($id);

        return view('acessorio.show', ['acessorio' => $acessorio]);
    }

    /** CREATE - formulario */
    public function create()
    {
        return view('acessorio.form', $this->listas() + ['acessorio' => null]);
    }

    /** CREATE - grava */
    public function store(Request $request)
    {
        Acessorio::create($this->validar($request));

        return redirect()
            ->route('acessorio.index')
            ->with('success', 'Acessorio cadastrado com sucesso!');
    }

    /** UPDATE - formulario */
    public function edit(int $id)
    {
        return view('acessorio.form', $this->listas() + ['acessorio' => Acessorio::findOrFail($id)]);
    }

    /** UPDATE - grava */
    public function update(Request $request, int $id)
    {
        Acessorio::findOrFail($id)->update($this->validar($request));

        return redirect()
            ->route('acessorio.index')
            ->with('success', 'Acessorio atualizado com sucesso!');
    }

    /** DELETE */
    public function destroy(int $id)
    {
        Acessorio::findOrFail($id)->delete();

        return redirect()
            ->route('acessorio.index')
            ->with('success', 'Acessorio excluido com sucesso!');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nome'         => 'required|string|max:100',
            'Plataforma'   => 'required|exists:plataforma,id',
            'quantidade'   => 'required|integer|min:0',
            'estado'       => 'required|exists:usado,id',
            'vintage'      => 'required|exists:retro,id',
            'cores'        => 'required|exists:cor,id',
            'colecionador' => 'required|exists:edicao_especial,id',
        ]);
    }

    private function listas(): array
    {
        return [
            'plataformas' => plataforma::orderBy('nome')->get(),
            'usados'   => Usado::orderBy('nome')->get(),
            'retros'   => Retro::orderBy('nome')->get(),
            'cores'    => Cor::orderBy('nome')->get(),
            'edicoes'  => EdicaoEspecial::orderBy('nome')->get(),
        ];
    }
}
