<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControleHistorico extends Model
{
    protected $table = 'controle_historico';

    public $timestamps = false;

    protected $fillable = [
        'controle_id',
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

    public function controle()
    {
        return $this->belongsTo(Controle::class, 'controle_id');
    }
}