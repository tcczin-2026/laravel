<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retro extends Model
{
    use HasFactory;

    protected $table = 'retro';
    public $timestamps = false;
    protected $fillable = ['nome'];

    public function consoles()
    {
        return $this->hasMany(Console::class, 'vintage');
    }

    public function controles()
    {
        return $this->hasMany(Controle::class, 'vintage');
    }

    public function jogos()
    {
        return $this->hasMany(Jogo::class, 'vintage');
    }

    public function acessorios()
    {
        return $this->hasMany(Acessorio::class, 'vintage');
    }
}
