<?php
namespace App\Http\Requests;

class OrdenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_producto' => 'required',
            'cantidad_planificada' => 'required|positive',
            'fecha' => 'required|date',
            'turno' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'id_producto' => 'El producto es obligatorio',
            'cantidad_planificada' => 'La cantidad debe ser un número positivo',
            'fecha' => 'La fecha es obligatoria',
            'turno' => 'El turno es obligatorio',
        ];
    }
}
