<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    protected $table = "equipe";

    protected $fillable = ['nome', 'documento', 'aniversario', 'login', 'senha'];

    public $timestamps = false;

    public function teste()
    {
        return "teste";
    }
}
