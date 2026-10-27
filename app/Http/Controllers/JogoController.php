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

    // Filtro por id exato (usado pelo link do histórico geral)
    $query->when($request->id, function ($q, $id) {
        $q->where('id', $id);
    });

    // Filtro por código de barras (exato; o leitor manda só os números)
    $query->when(trim((string) $request->codigo_barras), function ($q, $codigo) {
        $q->where('codigo_barras', $codigo);
    });

    // Filtro por plataforma (o formulário envia "plataformajogo")
    $query->when($request->plataformajogo, function ($q, $plataforma) {
        $q->where('Plataforma', $plataforma);
    });

    // Filtro por edição especial
    $query->when($request->edicoes, function ($q, $edicao) {
        $q->where('colecionador', $edicao);
    });

    // Filtro por retrocompatibilidade
    $query->when($request->retros, function ($q, $retro) {
        $q->where('vintage', $retro);
    });

    // Filtro por estado (usado/novo)
    $query->when($request->estado, function ($q, $estado) {
        $q->where('estado', $estado);
    });

    // Filtro por faixa de quantidade em estoque
    $query->when($request->quantidade_min, function ($q, $min) {
        $q->where('quantidade', '>=', $min);
    });

    $jogos = $query->orderBy('id')->paginate($this->porPagina())->withQueryString();

    return view('jogo.index', $this->listas() + [
        'jogos' => $jogos,
    ]);
}

    /** READ - detalhes de um item, com o histórico de alterações */
    public function show(int $id)
    {
        return view('jogo.show', ['jogo' => Jogo::with('historico')->findOrFail($id)]);
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
        $this->normalizarValor($request);

        return $request->validate([
            'nome'         => 'required|string|max:100',
            'Plataforma'   => 'required|exists:plataforma,id',
            'quantidade'   => 'required|integer|min:0',
            'valor'         => self::REGRA_VALOR,
            'codigo_barras' => self::REGRA_CODIGO_BARRAS,
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
