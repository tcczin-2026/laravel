<?php

namespace App\Models;

use App\Models\Concerns\TemValor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Controle extends Model
{
    use HasFactory, TemValor;

    protected $table = 'controle';
    public $timestamps = false;

    // Ação gravada no histórico no próximo save (ex: 'revertido'). Não é coluna do banco.
    public ?string $acaoHistorico = null;
    protected $fillable = [
        'nome',
        'plataformaControle',
        'quantidade',
        'valor',
        'cores',
        'vintage',
        'dispositivo',
        'estado',
        'colecionador',
        'imagem',
    ];

    public function plataforma()
    {
        return $this->belongsTo(plataforma::class, 'plataformaControle');
    }

    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cores');
    }

    public function retro()
    {
        return $this->belongsTo(Retro::class, 'vintage');
    }

    public function usado()
    {
        return $this->belongsTo(Usado::class, 'estado');
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
        return $this->hasMany(controleHistorico::class, 'controle_id')->latest();
    }
}
