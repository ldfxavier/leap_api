<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    protected $table = "notificacao";

    protected $fillable = ['codigo'];

    public $timestamps = false;

    public function teste()
    {
        return "teste";
    }
}
