<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;
use PagSeguro\Helpers\Validate;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = "usuario";

    public $timestamps = false;

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
            if (empty($data->cpf)) :
                return response([
                    'error' => true,
                    'message' => 'O campo "CPF" não pode ser vazio!',
                ], 400);
            else :
                $data->cpf = preg_replace("/[^0-9]/", "", $data->cpf);
            endif;

            $validateCpf = validateCpf($data->cpf);
            if (!$validateCpf) :
                return response([
                    'error' => true,
                    'message' => 'O "CPF" informado não é válido!',
                ], 400);
            endif;

            if (empty($data->telefone)) :
                return response([
                    'error' => true,
                    'message' => 'O campo "Telefone" não pode ser vazio!',
                ], 400);
            else :
                $data->telefone = preg_replace("/[^0-9]/", "", $data->telefone);
            endif;

            if (empty($data->nascimento)) :
                return response([
                    'error' => true,
                    'message' => 'O campo "Data de nascimento" não pode ser vazio!',
                ], 400);
            else :
                $data->nascimento = date('Y-d-m', strtotime($data->nascimento));
            endif;

            $this->uuid = Str::uuid();
            $this->nome = $data->nome;
            $this->email = $data->email;
            $this->cpf = str_pad($data->cpf, 11, '0', STR_PAD_LEFT);
            $this->data_nascimento = $data->nascimento;
            $this->telefone = $data->telefone;
            $this->status = 2;
            $this->data_criacao = date("Y-m-d H:i:s");
            $this->timestamps = false;

            if (null !== $data->file('avatar')) :
                $this->avatar = $data->file('avatar')->store('avatars');
            endif;

            if (empty($data->password)) :
                return response([
                    'error' => true,
                    'message' => 'O campo "Senha" não pode ser vazio!',
                ], 400);
            endif;
            $this->password = Hash::make($data->password);
            if ($this->save()) :

                $credentials = ['email' => $data->email, 'password' => $data->password];

                if (!$token = auth('api')->attempt($credentials)) {
                    return response(
                        [
                            'error' => true,
                            'message' => 'Login ou senha incorreto!',
                        ],
                        401
                    );
                }

                $user = auth('api')->user();
                $user->avatar = null !== $user->avatar ? asset('storage/' . $user->avatar) : null;

                return response([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'dados' => $user,
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

            $cep = trim($data->cep);
            $cep = str_replace("-", "", $cep);
            $cep = str_replace(".", "", $cep);

            $update = [
                'nome' => $data->nome,
                'telefone' => $telefone,
                'logradouro' => $data->logradouro,
                'numero' => $data->numero,
                'bairro' => $data->bairro,
                'complemento' => $data->complemento,
                'cidade' => $data->cidade,
                'estado' => $data->estado,
                'cep' => $cep,
            ];

            $id = auth('api')->user()->id;

            if ($this->where('id', $id)->update($update)) :
                return response([
                    'error' => false,
                    'message' => 'Dados atualizados com sucesso!',
                ]);
            else :
                return response([
                    'error' => false,
                    'message' => 'Nenhum dado atualizado!',
                ]);

            endif;
        } catch (Exception $e) {
            return response(sqlError($e->errorInfo), 400);
        }
    }

    /**
     * Update user avatar.
     *
     * @return array
     */
    public function avatar($data)
    {
        try {
            $this->timestamps = false;
            $update['avatar'] = $data->file('avatar')->store('avatars');

            $id = auth('api')->user()->id;

            if ($this->where('id', $id)->update($update)) :
                return response([
                    'error' => false,
                    'message' => 'Avatar atualizado com sucesso!',
                    'url' => asset('storage/' . $update['avatar'])
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
                $user->avatar = null !== $user->avatar ? asset('storage/' . $user->avatar) : null;
                return response($user);
            endif;
        } catch (Exception $e) {
            return response([
                'error' => true,
                'message' => 'Você não tem permissão para acessar esse usuário!'
            ], 401);
        }
    }

    /**
     * Show user.
     *
     * @return array
     */
    public function me()
    {
        try {
            $user = auth('api')->user();

            if ($user) :
                $user->avatar = null !== $user->avatar ? asset('storage/' . $user->avatar) : null;
                $user->data_nascimento =  date('d/m/Y', strtotime($user->data_nascimento));
                $user->cpf = str_pad($user->cpf, 11, '0', STR_PAD_LEFT);
                return response($user);
            endif;
        } catch (Exception $e) {
            return response([
                'error' => true,
                'message' => 'Você não tem permissão para acessar esse usuário!'
            ], 401);
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
