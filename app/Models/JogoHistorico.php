<?php

namespace App\Models;

use App\Models\Concerns\FormataHistorico;
use Illuminate\Database\Eloquent\Model;

class JogoHistorico extends Model
{
    use FormataHistorico; // $item->campo_rotulo, de_texto, para_texto (nomes em vez de ids)

    protected $table = 'jogo_historico';

    public $timestamps = false;

    protected $fillable = [
        'jogo_id',
        'acao',
        'campo',
        'valor_anterior',
        'valor_novo',
        'usuario',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function jogo()
    {
        return $this->belongsTo(Jogo::class, 'jogo_id');
    }
}