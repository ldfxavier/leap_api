<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = "usuario";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }



    /**
     * Create user.
     *
     * @return array
     */
    public function store($data)
    {
        try {
            $this->uuid = Str::uuid();
            $this->nome = $data->nome;
            $this->email = $data->email;
            $this->telefone = $data->telefone;
            $this->status = 1;
            $this->timestamps = false;

            if (empty($data->password)) :
                return response([
                    'message' => 'O campo "password" não pode ser vazio!',
                ], 400);
            endif;
            $this->password = Hash::make($data->password);
            if ($this->save()) :

                $credentials = ['email' => $data->email, 'password' => $data->password];

                if (!$token = auth('api')->attempt($credentials)) {
                    return response(
                        [
                            'message' => 'Login ou senha incorreto!',
                        ],
                        401
                    );
                }

                return response([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'dados' => auth('api')->user(),
                    'message' => 'Usuário criado com sucesso!'
                ]);
            endif;
        } catch (Exception $e) {
            return response(sqlError($e->errorInfo), 400);
        }
    }

    /**
     * Update user.
     *
     * @return array
     */
    public function updateUser($data)
    {
        try {
            $this->timestamps = false;
            $telefone = trim($data->telefone);
            $telefone = str_replace("(", "", $telefone);
            $telefone = str_replace(")", "", $telefone);
            $telefone = str_replace("-", "", $telefone);
            $telefone = str_replace(" ", "", $telefone);

            $update = [
                'nome' => $data->nome,
                'telefone' => $telefone,
            ];

            $id = auth('api')->user()->id;
            if ($this->where('id', $id)->update($update)) :
                return response([
                    'message' => 'Dados atualizados com sucesso!',
                ]);
            endif;
        } catch (Exception $e) {
            return response(sqlError($e->errorInfo), 400);
        }
    }

    /**
     * Show user.
     *
     * @return array
     */
    public function show($id)
    {
        try {
            $user = $this->find($id);

            if ($user) :
                $user->image = asset('storage/' . $user->image);
                $user->address;
                $user->jobs;
                return response($user);
            endif;
        } catch (Exception $e) {
            return response(['message' => 'Você não tem permissão para acessar esse usuário!'], 401);
        }
    }

    /**
     * Update password.
     *
     * @return array
     */
    public function password($data)
    {
        try {

            $user = auth('api')->user();

            if (!Hash::check($data->senha_atual, $user->password)) {
                return response([
                    'error' => true,
                    'message' => 'A senha digitada está incorreta!',
                ], 401);
            } else if ($data->nova_senha !== $data->confirmar_senha) {
                return response([
                    'error' => true,
                    'message' => 'As senhas digitadas não são iguais!',
                ], 401);
            }
            $update = [
                'data_atualizacao' => date("Y-m-d H:i:s"),
                'password' => Hash::make($data->nova_senha),
            ];
            $this->timestamps = false;

            if ($this->where('id', $user->id)->update($update)) :
                return response([
                    'error' => false,
                    'message' => 'Senha atualizada com sucesso!',
                ]);
            endif;
        } catch (Exception $e) {
            return response(['message' => 'Você não tem permissão para acessar essa rota!'], 401);
        }
    }
}
