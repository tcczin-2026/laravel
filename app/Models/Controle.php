<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Controle extends Model
{
    use HasFactory;

    protected $table = 'controle';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'MarcaControle',
        'quantidade',
        'cores',
        'vintage',
        'dispositivo',
        'estado',
        'colecionador',
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'MarcaControle');
    }

    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cores');
    }

    public function retro()
    {
        return $this->belongsTo(Retro::class, 'vintage');
    }

    public function console()
    {
        return $this->belongsTo(Console::class, 'dispositivo');
    }

    public function usado()
    {
        return $this->belongsTo(Usado::class, 'estado');
    }

    public function edicaoEspecial()
    {
        return $this->belongsTo(EdicaoEspecial::class, 'colecionador');
    }
}
