<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class checkPlano
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth('api')->user();

        $data = date('Y-m-d');

        if ($user->status !== 1) :
            return response([
                'error' => 5402,
                'message' => 'Você não tem autorização para acessar essa página!',
            ], 401);
        elseif (strtotime($data) > strtotime($user->data_vencimento)) :
            if ($user->status === 1) :
                $update = ['status' => 3];
                $User = new User();
                $User->where('id', $user->id)->update($update);
            endif;
            return response([
                'error' => 5402,
                'message' => 'Você não tem autorização para acessar essa página!',
            ], 401);
        endif;

        return $next($request);
    }
}
