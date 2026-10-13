<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jogo extends Model
{
    use HasFactory;

    protected $table = 'jogo';
    public $timestamps = false;

    // Ação gravada no histórico no próximo save (ex: 'revertido'). Não é coluna do banco.
    public ?string $acaoHistorico = null;
    protected $fillable = [
        'nome',
        'Plataforma',
        'quantidade',
        'estado',
        'vintage',
        'colecionador',
        'imagem',
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

    public function edicaoEspecial()
    {
        return $this->belongsTo(EdicaoEspecial::class, 'colecionador');
    }
    public function getImagemUrlAttribute(): ?string
    {
        return $this->imagem ? asset('storage/' . $this->imagem) : null;
    }

    public function historico()
    {
        return $this->hasMany(JogoHistorico::class, 'jogo_id')->latest();
    }
}
