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

        /** READ - lista com filtros */
        public function index(Request $request)
        {
            $query = acessorio::with([
                'plataforma', 'usado', 'cor', 'retro', 'edicaoEspecial','historico',
            ]);
    
            // Filtro por nome (busca parcial)
            $query->when($request->nome, function ($q, $nome) {
                $q->where('nome', 'like', "%{$nome}%");
            });
    
            // Filtro por plataforma
            $query->when($request->plataformaacessorio, function ($q, $plataforma) {
                $q->where('plataformaacessorio', $plataforma);
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
    
            $acessorios = $query->orderBy('id')->get();
    
            return view('acessorio.index', $this->listas() + [
                'acessorios' => $acessorios,
            ]);
        }

 
    /** CREATE - formulario */
    public function create()
    {
        return view('acessorio.form', $this->listas() + ['acessorio' => null]);
    }
 
    /** CREATE - grava */
    public function store(Request $request)
    {
        acessorio::create($this->validar($request));
 
        return redirect()
            ->route('acessorio.index')
            ->with('success', 'acessorio cadastrado com sucesso!');
    }
 
    /** UPDATE - formulario */
    public function edit(int $id)
    {
        return view('acessorio.form', $this->listas() + ['acessorio' => acessorio::findOrFail($id)]);
    }
 
    /** UPDATE - grava */
    public function update(Request $request, int $id)
    {
        acessorio::findOrFail($id)->update($this->validar($request));
 
        return redirect()
            ->route('acessorio.index')
            ->with('success', 'acessorio atualizado com sucesso!');
    }
 
    /** DELETE */
    public function destroy(int $id)
    {
        acessorio::findOrFail($id)->delete();
 
        return redirect()
            ->route('acessorio.index')
            ->with('success', 'acessorio excluido com sucesso!');
    }
 
    private function validar(Request $request): array
    {
        return $request->validate([
            'nome'          => 'required|string|max:100',
            'Plataforma' => 'required|exists:plataforma,id',
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
 