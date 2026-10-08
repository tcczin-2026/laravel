<?php

namespace App\Http\Controllers;

use App\Models\plataforma;
use App\Models\EdicaoEspecial;
use App\Models\Jogo;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JogoController extends Controller
{
/** READ - lista com filtros */
public function index(Request $request)
{
    $query = Jogo::with([
        'plataforma', 'usado', 'retro', 'edicaoEspecial','historico',
    ]);

    // Filtro por nome (busca parcial)
    $query->when($request->nome, function ($q, $nome) {
        $q->where('nome', 'like', "%{$nome}%");
    });

    // Filtro por plataforma
    $query->when($request->plataforma, function ($q, $plataforma) {
        $q->where('plataforma', $plataforma);
    });

    // Filtro por estado (usado/novo)
    $query->when($request->estado, function ($q, $estado) {
        $q->where('estado', $estado);
    });

    // Filtro por faixa de quantidade em estoque
    $query->when($request->quantidade_min, function ($q, $min) {
        $q->where('quantidade', '>=', $min);
    });

    $jogos = $query->orderBy('id')->paginate(10)->withQueryString();

    return view('jogo.index', $this->listas() + [
        'jogos' => $jogos,
    ]);
}

    /** CREATE - formulario */
    public function create()
    {
        return view('jogo.form', $this->listas() + ['jogo' => null]);
    }

    /** CREATE - grava */
    public function store(Request $request)
    {
        $dados = $this->validar($request);

        if ($request->hasFile('imagem')) {
            $dados['imagem'] = $request->file('imagem')->store('jogos', 'public');
        }

        Jogo::create($dados);

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
        $jogo = Jogo::findOrFail($id);
        $dados = $this->validar($request);

        if ($request->hasFile('imagem')) {
            if ($jogo->imagem) {
                Storage::disk('public')->delete($jogo->imagem); // apaga a antiga
            }
            $dados['imagem'] = $request->file('imagem')->store('jogos', 'public');
        }

        $jogo->update($dados);

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
            'Plataforma'   => 'required|exists:plataforma,id',
            'quantidade'   => 'required|integer|min:0',
            'estado'       => 'required|exists:usado,id',
            'vintage'      => 'required|exists:retro,id',
            'colecionador' => 'required|exists:edicao_especial,id',
            'imagem'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    }

    private function listas(): array
    {
        return [
            'plataformas' => plataforma::orderBy('nome')->get(),
            'usados'   => Usado::orderBy('nome')->get(),
            'retros'   => Retro::orderBy('nome')->get(),
            'edicoes'  => EdicaoEspecial::orderBy('nome')->get(),
        ];
    }
}
