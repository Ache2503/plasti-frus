<?php
namespace App\Http\Requests;

class ClienteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'razon_social' => 'required',
            'rfc' => 'required|rfc',
            'email' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'razon_social' => 'La razón social es obligatoria',
            'rfc' => 'El RFC es obligatorio y debe ser válido',
            'email' => 'El email es obligatorio y debe ser válido',
        ];
    }
}
