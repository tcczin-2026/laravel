<?php

namespace App\Http\Controllers;

use App\Models\Console;
use App\Models\EdicaoEspecial;
use App\Models\Jogo;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Http\Request;

class JogoController extends Controller
{
    /** READ - lista */
    public function index()
    {
        $jogos = Jogo::with(['console', 'usado', 'retro', 'edicaoEspecial'])
            ->orderBy('nome')
            ->get();

        return view('jogo.index', ['jogos' => $jogos]);
    }

    /** READ - detalhe */
    public function show(int $id)
    {
        $jogo = Jogo::with(['console', 'usado', 'retro', 'edicaoEspecial'])->findOrFail($id);

        return view('jogo.show', ['jogo' => $jogo]);
    }

    /** CREATE - formulario */
    public function create()
    {
        return view('jogo.form', $this->listas() + ['jogo' => null]);
    }

    /** CREATE - grava */
    public function store(Request $request)
    {
        Jogo::create($this->validar($request));

        return redirect()
            ->route('jogo.index')
            ->with('success', 'Jogo cadastrado com sucesso!');
    }

    /** UPDATE - formulario */
    public function edit(int $id)
    {
        return view('jogo.form', $this->listas() + ['jogo' => Jogo::findOrFail($id)]);
    }

    /** UPDATE - grava */
    public function update(Request $request, int $id)
    {
        Jogo::findOrFail($id)->update($this->validar($request));

        return redirect()
            ->route('jogo.index')
            ->with('success', 'Jogo atualizado com sucesso!');
    }

    /** DELETE */
    public function destroy(int $id)
    {
        Jogo::findOrFail($id)->delete();

        return redirect()
            ->route('jogo.index')
            ->with('success', 'Jogo excluido com sucesso!');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nome'         => 'required|string|max:100',
            'Plataforma'   => 'required|exists:console,id',
            'quantidade'   => 'required|integer|min:0',
            'estado'       => 'required|exists:usado,id',
            'vintage'      => 'required|exists:retro,id',
            'colecionador' => 'required|exists:edicao_especial,id',
        ]);
    }

    private function listas(): array
    {
        return [
            'consoles' => Console::orderBy('nome')->get(),
            'usados'   => Usado::orderBy('nome')->get(),
            'retros'   => Retro::orderBy('nome')->get(),
            'edicoes'  => EdicaoEspecial::orderBy('nome')->get(),
        ];
    }
}
