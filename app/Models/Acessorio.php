<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acessorio extends Model
{
    use HasFactory;

    protected $table = 'acessorio';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'Plataforma',
        'quantidade',
        'estado',
        'vintage',
        'cores',
        'colecionador',
    ];

    public function plataforma()
    {
        return $this->belongsTo(plataforma::class, 'Plataforma');
    }

    public function usado()
    {
        return $this->belongsTo(Usado::class, 'estado');
    }

    public function retro()
    {
        return $this->belongsTo(Retro::class, 'vintage');
    }

    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cores');
    }

    public function edicaoEspecial()
    {
        return $this->belongsTo(EdicaoEspecial::class, 'colecionador');
    }
}
