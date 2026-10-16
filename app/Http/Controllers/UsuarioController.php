<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** CRUD de usuários - só o gerente acessa (middleware perfil:gerente nas rotas) */
class UsuarioController extends Controller
{
    public function index()
    {
        return view('usuario.index', [
            'usuarios' => User::orderBy('name')->paginate(10),
        ]);
    }

    public function create()
    {
        return view('usuario.form', ['usuario' => null]);
    }

    public function store(Request $request)
    {
        User::create($this->validar($request));

        return redirect()->route('usuario.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function edit(int $id)
    {
        return view('usuario.form', ['usuario' => User::findOrFail($id)]);
    }

    public function update(Request $request, int $id)
    {
        $usuario = User::findOrFail($id);
        $dados   = $this->validar($request, $usuario);

        // Senha em branco na edição = manter a atual
        if (empty($dados['password'])) {
            unset($dados['password']);
        }

        // Não deixa o sistema ficar sem nenhum gerente
        if ($usuario->isGerente() && $dados['perfil'] !== 'gerente' && $this->ultimoGerente()) {
            return back()->withInput()
                ->with('error', 'Este é o único gerente. Cadastre outro gerente antes de mudar o perfil dele.');
        }

        $usuario->update($dados);

        return redirect()->route('usuario.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(Request $request, int $id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'Você não pode excluir o seu próprio usuário.');
        }

        if ($usuario->isGerente() && $this->ultimoGerente()) {
            return back()->with('error', 'Não é possível excluir o único gerente.');
        }

        $usuario->delete();

        return redirect()->route('usuario.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        return $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario?->id)],
            'perfil'   => ['required', Rule::in(array_keys(User::PERFIS))],
            // obrigatória só no cadastro; na edição, em branco mantém a senha atual
            'password' => [$usuario ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'name'     => 'nome',
            'password' => 'senha',
        ]);
    }

    private function ultimoGerente(): bool
    {
        return User::where('perfil', 'gerente')->count() <= 1;
    }
}
