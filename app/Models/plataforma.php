<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class plataforma extends Model
{
    use HasFactory;

    protected $table = 'plataforma';
    public $timestamps = false;
    protected $fillable = ['nome'];

    public function consoles()
    {
        return $this->hasMany(Console::class, 'plataformaConsole');
    }

    public function controles()
    {
        return $this->hasMany(Controle::class, 'plataformaControle');
    }
}
