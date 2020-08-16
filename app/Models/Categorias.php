<?php

namespace App\Models;

use App\Models\Cursos;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Categorias extends Model
{
    protected $table = "curso_online_categorias";

    public function montar($dados, $tipo)
    {
        $array = [];
        if ($dados) :
            $Cursos = new Cursos;
            foreach ($dados as $r) :
                $cursos = $r->cursos($tipo);
                $array[] = (object)[
                    "id" => $r->id,
                    "titulo" => $r->titulo,
                    "url" => $r->url,
                    "imagem" => Config::get('constants.diretorio.arquivo') . "/online_categorias/" . $r->imagem,
                    "data_criacao" => (object)[
                        "valor" => $r->data_criacao,
                        "br" => Carbon::parse($r->data_criacao)->format('d/m/Y')
                    ],
                    "cursos" => $Cursos->montar($cursos),
                ];
            endforeach;
        endif;

        return $array;
    }

    public function categoriaCurso($tipo)
    {
        try {
            $Categorias = $this->where('status', '=', '1')->get();
            if ($Categorias) {
                return $this->montar($Categorias, $tipo);
            }
        } catch (Exception $e) {
            return response(['message' => 'Você não tem permissão para acessar esse curso!'], 401);
        }
    }

    public function cursos($tipo)
    {
        return $this->belongsToMany(Cursos::class, "categoria_curso", "categoria", "curso")->where('gratuito', '=', $tipo)->get();
    }
}
