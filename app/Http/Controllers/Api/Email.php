<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\newMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class Email extends Controller
{
    public function enviar()
    {
        $User = new User();
        $user = $User->where('id', 1)->get();

        // return new newMail($user[0]);

        Mail::send(new newMail($user[0]));
    }
}
