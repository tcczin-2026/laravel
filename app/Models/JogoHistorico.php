<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JogoHistorico extends Model
{
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