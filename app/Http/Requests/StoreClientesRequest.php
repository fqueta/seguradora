<?php

namespace App\Http\Requests;

use App\Rules\FullName;
use App\Rules\RightCpf;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientesRequest extends FormRequest
{


    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'name' => ['required','string',new FullName],
            'cpf'   =>[new  RightCpf,'required','unique:users']
        ];
    }
    public function messages()
    {
        return [
            'name.required'=>__('O nome é obrigatório'),
            'name.string'=>__('É necessário conter letras no nome'),
            'cpf.unique'=>__('CPF já cadastrado'),
        ];
    }
}
