<?php
namespace App\Http\Requests;

class VentaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_cliente' => 'required',
            'id_producto' => 'required',
            'cantidad_vendida' => 'required|positive',
            'precio_unitario' => 'required|positive',
        ];
    }

    public function messages(): array
    {
        return [
            'id_cliente' => 'El cliente es obligatorio',
            'id_producto' => 'El producto es obligatorio',
            'cantidad_vendida' => 'La cantidad debe ser un número positivo',
            'precio_unitario' => 'El precio debe ser un número positivo',
        ];
    }
}
