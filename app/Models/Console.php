<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    use HasFactory;

    protected $table = 'console';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'plataformaConsole',
        'quantidade',
        'estado',
        'leitor',
        'cores',
        'vintage',
        'aberto',
        'colecionador',
    ];

    public function plataforma()
    {
        return $this->belongsTo(plataforma::class, 'plataformaConsole');
    }

    public function usado()
    {
        return $this->belongsTo(Usado::class, 'estado');
    }

    public function digital()
    {
        return $this->belongsTo(Digital::class, 'leitor');
    }

    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cores');
    }

    public function retro()
    {
        return $this->belongsTo(Retro::class, 'vintage');
    }

    public function desbloqueado()
    {
        return $this->belongsTo(Desbloqueado::class, 'aberto');
    }

    public function edicaoEspecial()
    {
        return $this->belongsTo(EdicaoEspecial::class, 'colecionador');
    }

    public function controles()
    {
        return $this->hasMany(Controle::class, 'dispositivo');
    }

    public function jogos()
    {
        return $this->hasMany(Jogo::class, 'Plataforma');
    }

    public function acessorios()
    {
        return $this->hasMany(Acessorio::class, 'Plataforma');
    }

       /**
     * Histórico de alterações deste controle, do mais recente pro mais
     * antigo (->latest() ordena por created_at desc). É alimentado
     * automaticamente pelo ControleObserver a cada create/update/delete
     * — você nunca cria um registro de histórico na mão.
     */

    public function historico()
    {
        return $this->hasMany(ControleHistorico::class, 'controle_id')->latest();
    }
}
