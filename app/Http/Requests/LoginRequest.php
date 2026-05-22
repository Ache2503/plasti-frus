<?php
namespace App\Http\Requests;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nombre_usuario' => 'required',
            'password' => 'required|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_usuario' => 'El usuario es obligatorio',
            'password' => 'La contraseña debe tener al menos 6 caracteres',
        ];
    }
}
