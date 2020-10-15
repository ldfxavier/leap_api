<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\newMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class Email extends Controller
{
    public function enviar(array $dados)
    {
        // $dados = [
        //     'titulo' => 'Pagamento aprovado!',
        //     'texto' => 'Você já pode acessar nossos cursos. Basta ir em nosso site, na área do aluno e entrar com seu usuário e senha!',
        //     'button' => (object)[
        //         'texto' => 'ÁREA DO ALUNO',
        //         'url' => 'https://leap.art.br/login'
        //     ],
        //     'nome' => 'Lucas Xavier',
        //     'email' => 'ldfxavier@gmail.com'
        // ];
        // return new newMail((object)$dados);

        Mail::send(new newMail((object)$dados));
    }
}
