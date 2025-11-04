<?php

namespace App\Http\Requests;

use App\Rules\FullName;
use App\Rules\RightCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->sanitize();

        return [
            'name'=>['required',new FullName],
            'email'=>['required','string','unique:users'],
            'cpf'=>['required',new RightCpf],
            'password'=>['required', 'confirmed', Password::min(6)],
            'password_confirmation'=>['required'],

        ];
    }
    public function messages()
    {
        return [
            'name.required' => __('O Nome é obrigatório'),
            'cpf.unique' => __('Este CPF já está sendo utilizado'),
            'email.required' => __('O Email é obrigatório'),
            'email.unique' => __('O Email já está cadastrado'),
            'password.required' => __('A Senha é obribatória'),
            'password_confirmation.required' => __('É obribatório confirmar a senha'),
            //'password_conf.confirmed' => __('As senhas são diferentes'),
        ];
    }
    public function sanitize()
    {
        $data = $this->all();
        foreach ($data as $key => $value) {
            if(is_array($value)){
                foreach ($value as $k => $v) {
                    $data[$key][$k] = strip_tags($v);
                    $data[$key][$k] = addslashes($data[$key][$k]);
                }
            }else{
                $data[$key] = strip_tags($value);
                $data[$key] = addslashes($data[$key]);
            }
        }
        $this->replace($data);
    }
}
