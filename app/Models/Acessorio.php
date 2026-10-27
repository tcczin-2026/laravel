<?php

namespace App\Models;

use App\Models\Concerns\TemValor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acessorio extends Model
{
    use HasFactory, TemValor;

    protected $table = 'acessorio';
    public $timestamps = false;

    // Ação gravada no histórico no próximo save (ex: 'revertido'). Não é coluna do banco.
    public ?string $acaoHistorico = null;
    protected $fillable = [
        'nome',
        'codigo_barras',
        'Plataforma',
        'quantidade',
        'valor',
        'estado',
        'vintage',
        'cores',
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

    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cores');
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
        return $this->hasMany(AcessorioHistorico::class, 'acessorio_id')->latest();
    }
}
