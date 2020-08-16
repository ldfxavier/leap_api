<?php

namespace App\Models;

use App\Models\Aulas;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Cursos extends Model
{
    protected $table = "curso_online_cursos";

    public $timestamps = false;


    public function montar($dados)
    {
        $array = [];
        if ($dados) :
            foreach ($dados as $r) :
                $array[] = (object)[
                    "url" => $r->url,
                    "professor" => $r->professor,
                    "titulo" => $r->titulo,
                    "texto" => $r->texto,
                    "chamada" => $r->chamada,
                    "imagem" => Config::get('constants.diretorio.arquivo') . "/online_cursos/" . $r->imagem,
                    "data_criacao" => (object)[
                        "valor" => $r->data_criacao,
                        "br" => Carbon::parse($r->data_criacao)->format('d/m/Y')
                    ]
                ];
            endforeach;
        endif;

        return $array;
    }

    /**
     * Show curso.
     *
     * @return array
     */
    public function show($url)
    {
        try {

            $Cursos = $this->where('status', '=', '1')->where('url', '=', $url)->first();

            if ($Cursos) :
                $dados_categoria = $Cursos->categorias;
                $dados_aulas = $Cursos->aulas;

                $categorias = [];
                $aulas = [];

                foreach ($dados_categoria as $r_categoria) :
                    $categorias[] = (object)[
                        "id" => $r_categoria->id,
                        "titulo" => $r_categoria->titulo,
                        "imagem" => Config::get('constants.diretorio.arquivo') . "/online_categorias/" . $r_categoria->imagem
                    ];
                endforeach;

                foreach ($dados_aulas as $r_aulas) :
                    $aulas[] = (object)[
                        "id" => $r_aulas->id,
                        "titulo" => $r_aulas->titulo,
                        "video" => $r_aulas->video,
                        "conteudo" => $r_aulas->conteudo,
                        "dica" => $r_aulas->dica,
                        "assistido" => $r_aulas->assistido,
                        "data_criacao" => (object)[
                            "valor" => $r_aulas->data_criacao,
                            "br" => Carbon::parse($r_aulas->data_criacao)->format('d/m/Y')
                        ],
                    ];
                endforeach;


                $array = (object)[
                    "url" => $Cursos->url,
                    "professor" => $Cursos->professor,
                    "titulo" => $Cursos->titulo,
                    "texto" => $Cursos->texto,
                    "chamada" => $Cursos->chamada,
                    "imagem" => Config::get('constants.diretorio.arquivo') . "/online_cursos/" . $Cursos->imagem,
                    "data_criacao" => (object)[
                        "valor" => $Cursos->data_criacao,
                        "br" => Carbon::parse($Cursos->data_criacao)->format('d/m/Y')
                    ],
                    "categorias" => $categorias,
                    "aulas" => $aulas
                ];

                return response()->json($array);
            endif;
        } catch (Exception $e) {
            return response(['message' => 'Você não tem permissão para acessar esse curso!'], 401);
        }
    }

    public function busca($titulo)
    {
        try {
            $Cursos = $this->where('status', '=', '1')->where('titulo', 'LIKE', "%{$titulo}%")->get();

            return response()->json($this->montar($Cursos));
        } catch (Exception $e) {
            return response(['message' => 'Você não tem permissão para acessar esse curso!'], 401);
        }
    }

    public function categorias()
    {
        return $this->belongsToMany(Categorias::class, "categoria_curso", "curso", "categoria");
    }

    public function aulas()
    {
        return $this->hasMany(Aulas::class, "curso", "id");
    }
}
