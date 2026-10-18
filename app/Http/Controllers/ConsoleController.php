<?php

namespace App\Http\Controllers;

use App\Models\Acessorio;
use App\Models\Console;
use App\Models\Jogo;
use App\Models\Cor;
use App\Models\Desbloqueado;
use App\Models\Digital;
use App\Models\EdicaoEspecial;
use App\Models\plataforma;
use App\Models\Retro;
use App\Models\Usado;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConsoleController extends Controller
{
    /** READ - lista com filtros */
    public function index(Request $request)
    {
        $query = Console::with([
            'plataforma', 'usado', 'digital', 'cor', 'retro', 'desbloqueado', 'edicaoEspecial', 'historico',
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

        // Filtro por leitor (digital)
        $query->when($request->digitais, function ($q, $digital) {
            $q->where('leitor', $digital);
        });

        // Filtro por desbloqueado
        $query->when($request->desbloqueados, function ($q, $desbloqueado) {
            $q->where('aberto', $desbloqueado);
        });

        // Filtro por edição especial
        $query->when($request->edicoes, function ($q, $edicao) {
            $q->where('colecionador', $edicao);
        });

        // Filtro por retrocompatibilidade
        $query->when($request->retros, function ($q, $retro) {
            $q->where('vintage', $retro);
        });

        // Filtro por faixa de quantidade em estoque
        $query->when($request->quantidade_min, function ($q, $min) {
            $q->where('quantidade', '>=', $min);
        });

        $consoles = $query->orderBy('id')->paginate($this->porPagina())->withQueryString();

        return view('console.index', $this->listas() + [
            'consoles' => $consoles,
        ]);
    }


    /** READ - detalhes de um item, com o histórico de alterações */
    public function show(int $id)
    {
        $console = Console::with('historico', 'controles')->findOrFail($id);

        // Jogos e acessórios ficam ligados à plataforma (não ao console),
        // então mostramos os da mesma plataforma deste console
        return view('console.show', [
            'console'    => $console,
            'jogos'      => Jogo::where('Plataforma', $console->plataformaConsole)->orderBy('nome')->get(),
            'acessorios' => Acessorio::where('Plataforma', $console->plataformaConsole)->orderBy('nome')->get(),
        ]);
    }

    /** CREATE - formulario */
    public function create()
    {
        return view('console.form', $this->listas() + ['console' => null]);
    }

    /** CREATE - grava */
    public function store(Request $request)
    {
        $dados = $this->validar($request);

        if ($request->hasFile('imagem')) {
            $dados['imagem'] = $request->file('imagem')->store('consoles', 'public');
        }



        Console::create($dados);

        return redirect()->route('console.index')
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
        $console = Console::findOrFail($id);
        $dados = $this->validar($request);

        if ($request->hasFile('imagem')) {
            if ($console->imagem) {
                Storage::disk('public')->delete($console->imagem); // apaga a antiga
            }
            $dados['imagem'] = $request->file('imagem')->store('consoles', 'public');
        }

        $console->update($dados);

        return redirect()->route('console.index')
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
        $this->normalizarValor($request);

        return $request->validate([
            'nome'         => 'required|string|max:100',
            'plataformaConsole' => 'required|exists:plataforma,id',
            'quantidade'   => 'required|integer|min:0',
            'valor'         => self::REGRA_VALOR,
            'armazenamento' => 'nullable|integer|min:1|max:100000', // em GB
            'estado'       => 'required|exists:usado,id',
            'leitor'       => 'required|exists:digital,id',
            'cores'        => 'required|exists:cor,id',
            'vintage'      => 'required|exists:retro,id',
            'aberto'       => 'required|exists:desbloqueado,id',
            'colecionador' => 'required|exists:edicao_especial,id',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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

