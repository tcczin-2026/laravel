<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cor extends Model
{
    use HasFactory;

    protected $table = 'cor';
    public $timestamps = false;
    protected $fillable = ['nome'];

    public function consoles()
    {
        return $this->hasMany(Console::class, 'cores');
    }

    public function controles()
    {
        return $this->hasMany(Controle::class, 'cores');
    }

    public function acessorios()
    {
        return $this->hasMany(Acessorio::class, 'cores');
    }
}
