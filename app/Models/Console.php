<?php

namespace App\Models;

use App\Models\Concerns\TemValor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    use HasFactory, TemValor;

    protected $table = 'console';
    public $timestamps = false;

    // Ação gravada no histórico no próximo save (ex: 'revertido'). Não é coluna do banco.
    public ?string $acaoHistorico = null;
    protected $fillable = [
        'nome',
        'codigo_barras',
        'plataformaConsole',
        'quantidade',
        'valor',
        'armazenamento',
        'estado',
        'leitor',
        'cores',
        'vintage',
        'aberto',
        'colecionador',
        'imagem',
        'historico',

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

    // Jogos e acessórios não ligam no console, e sim na plataforma
    // (coluna "Plataforma" = id da tabela plataforma). Veja ConsoleController@show.

    /** "825 GB", "1 TB", "1,5 TB" (ou "-" se não tem) */
    public function getArmazenamentoFormatadoAttribute(): string
    {
        $gb = $this->armazenamento;

        if (! $gb) {
            return '-';
        }

        return $gb >= 1000
            ? rtrim(rtrim(number_format($gb / 1000, 1, ',', '.'), '0'), ',') . ' TB'
            : $gb . ' GB';
    }

    public function getImagemUrlAttribute(): ?string
    {   
        return $this->imagem ? asset('storage/' . $this->imagem) : null;
    }   

       /**
     * Histórico de alterações deste console, do mais recente pro mais
     * antigo (->latest() ordena por created_at desc). É alimentado
     * automaticamente pelo consoleObserver a cada create/update/delete
     * — você nunca cria um registro de histórico na mão.
     */

    public function historico()
    {
        return $this->hasMany(ConsoleHistorico::class, 'console_id')->latest();
    }
}
