<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jogo extends Model
{
    use HasFactory;

    protected $table = 'jogo';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'Plataforma',
        'quantidade',
        'estado',
        'vintage',
        'colecionador',
    ];

    public function console()
    {
        return $this->belongsTo(Console::class, 'Plataforma');
    }

    public function usado()
    {
        return $this->belongsTo(Usado::class, 'estado');
    }

    public function retro()
    {
        return $this->belongsTo(Retro::class, 'vintage');
    }

    public function edicaoEspecial()
    {
        return $this->belongsTo(EdicaoEspecial::class, 'colecionador');
    }
}
