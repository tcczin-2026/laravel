<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    use HasFactory;

    protected $table = 'console';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'MarcaConsole',
        'quantidade',
        'estado',
        'leitor',
        'cores',
        'vintage',
        'aberto',
        'colecionador',
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'MarcaConsole');
    }

    public function usado()
    {
        return $this->belongsTo(Usado::class, 'estado');
    }

    public function digital()
    {
        return $this->belongsTo(Digital::class, 'leitor');
    }

    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cores');
    }

    public function retro()
    {
        return $this->belongsTo(Retro::class, 'vintage');
    }

    public function desbloqueado()
    {
        return $this->belongsTo(Desbloqueado::class, 'aberto');
    }

    public function edicaoEspecial()
    {
        return $this->belongsTo(EdicaoEspecial::class, 'colecionador');
    }

    public function controles()
    {
        return $this->hasMany(Controle::class, 'dispositivo');
    }

    public function jogos()
    {
        return $this->hasMany(Jogo::class, 'Plataforma');
    }

    public function acessorios()
    {
        return $this->hasMany(Acessorio::class, 'Plataforma');
    }
}
