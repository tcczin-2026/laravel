<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'gerente'     -> mexe em tudo, inclusive usuários e tabelas auxiliares
            // 'funcionario' -> mexe só em console, controle, jogo e acessório
            $table->string('perfil', 20)->default('funcionario')->after('email');
        });

        // Sem nenhum usuário ninguém conseguiria entrar no sistema,
        // então cria um gerente padrão (troque a senha depois do 1º login).
        if (DB::table('users')->count() === 0) {
            DB::table('users')->insert([
                'name'       => 'Gerente',
                'email'      => 'gerente@colecao.com',
                'perfil'     => 'gerente',
                'password'   => Hash::make('gerente123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('perfil');
        });
    }
};
