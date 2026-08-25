<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usado extends Model
{
    use HasFactory;

    protected $table = 'usado';
    public $timestamps = false;
    protected $fillable = ['nome'];

    public function consoles()
    {
        return $this->hasMany(Console::class, 'estado');
    }

    public function controles()
    {
        return $this->hasMany(Controle::class, 'estado');
    }

    public function jogos()
    {
        return $this->hasMany(Jogo::class, 'estado');
    }

    public function acessorios()
    {
        return $this->hasMany(Acessorio::class, 'estado');
    }
}
