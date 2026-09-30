<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcessorioHistorico extends Model
{
    protected $table = 'acessorio_historico';

    // A tabela só tem created_at (sem updated_at) -> desliga o padrão do Eloquent
    public $timestamps = false;

    protected $fillable = [
        'acessorio_id',
        'acao',
        'campo',
        'valor_anterior',
        'valor_novo',
        'usuario',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime', // permite usar ->format() direto na view
    ];

    public function acessorio()
    {
        return $this->belongsTo(acessorio::class, 'acessorio_id');
    }
    public function historico()
    {
        return $this->hasMany(AcessorioHistorico::class, 'acessorio_id')->latest();
    }

    
}