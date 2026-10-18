<?php

namespace App\Models;

use App\Models\Concerns\FormataHistorico;
use Illuminate\Database\Eloquent\Model;

class ControleHistorico extends Model
{
    use FormataHistorico; // $item->campo_rotulo, de_texto, para_texto (nomes em vez de ids)

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