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
        // return new newMail((object)$dados);

        Mail::send(new newMail((object)$dados));
    }
}
