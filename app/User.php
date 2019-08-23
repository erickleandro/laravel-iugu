<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Potelo\GuPayment\GuPaymentTrait;

class User extends Authenticatable
{
    use Notifiable, GuPaymentTrait;

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

    public function cadastrarUsuario($request)
    {
        $existe = $this->where('email', $request->email)->get()->first();

        if ($existe) {            
            return $existe;
        }

        $this->name = $request->name;
        $this->email = $request->email;
        $this->password = bcrypt($request->password);
        $this->cpf = $request->cpf;
        $this->rua = $request->rua;
        $this->numero = $request->numero;
        $this->bairro = $request->bairro;
        $this->complemento = $request->complemento;
        $this->cep = $request->cep;
        $this->cidade = $request->cidade;
        $this->estado = $request->estado;
        $this->save();

        return $this;
    }

    public function assinarPlano($plano)
    {
        $subscriptionBuilder = $this->newSubscription($plano, $plano);

        $options = [
            'name' => $this->name,
            'street' => $this->rua,
            'district' => $this->bairro,
            'cpf_cnpj' => $this->cpf,
            'zip_code' => $this->cep,
            'number' => $this->numero,
        ];

        $assinatura = $subscriptionBuilder->create(null, $options);

        $url = null;

        if ($assinatura && isset($assinatura['recent_invoices'])) {
            foreach ($assinatura->recent_invoices as $fatura) {
                $url = $fatura->secure_url;   
            }
        }

        return $url;
    }
}
