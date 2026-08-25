<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EdicaoEspecial extends Model
{
    use HasFactory;

    protected $table = 'edicao_especial';
    public $timestamps = false;
    protected $fillable = ['nome'];

    public function consoles()
    {
        return $this->hasMany(Console::class, 'colecionador');
    }

    public function controles()
    {
        return $this->hasMany(Controle::class, 'colecionador');
    }

    public function jogos()
    {
        return $this->hasMany(Jogo::class, 'colecionador');
    }

    public function acessorios()
    {
        return $this->hasMany(Acessorio::class, 'colecionador');
    }
}
