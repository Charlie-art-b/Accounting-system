<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $supplierId = $this->route('record') ?? $this->route('supplier');

        return [
            'tipo_proveedor' => [
                'required',
                Rule::in(['persona', 'empresa']),
            ],
            'nombre_razon_social' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\-\&]+$/',
            ],
            'identificacion' => [
                'required',
                'string',
                'min:6',
                'max:50',
                'regex:/^[0-9\-]+$/',
                Rule::unique('suppliers', 'identificacion')->ignore($supplierId),
            ],
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('suppliers', 'correo')->ignore($supplierId),
            ],
            'telefono' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\+\-\(\)\s]+$/',
            ],
            'estado' => [
                'required',
                Rule::in(['activo', 'inactivo']),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo_proveedor.required' => 'El tipo de proveedor es obligatorio.',
            'tipo_proveedor.in' => 'El tipo de proveedor debe ser "persona" o "empresa".',

            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
            'nombre_razon_social.string' => 'El nombre debe ser un texto válido.',
            'nombre_razon_social.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre_razon_social.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre_razon_social.regex' => 'El nombre solo puede contener letras, espacios, puntos, guiones y &.',

            'identificacion.required' => 'La identificación es obligatoria.',
            'identificacion.string' => 'La identificación debe ser un texto válido.',
            'identificacion.min' => 'La identificación debe tener al menos 6 caracteres.',
            'identificacion.max' => 'La identificación no puede exceder 50 caracteres.',
            'identificacion.regex' => 'La identificación solo puede contener números y guiones.',
            'identificacion.unique' => 'La identificación ingresada ya está registrada.',

            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico debe ser un email válido.',
            'correo.max' => 'El correo no puede exceder 255 caracteres.',
            'correo.unique' => 'El correo electrónico ya está registrado.',

            'telefono.string' => 'El teléfono debe ser un texto válido.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'telefono.regex' => 'El teléfono solo puede contener números, +, -, paréntesis y espacios.',

            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser "activo" o "inactivo".',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir a minúsculas y trim
        $this->merge([
            'correo' => strtolower(trim($this->correo ?? '')),
            'nombre_razon_social' => trim($this->nombre_razon_social ?? ''),
            'identificacion' => trim($this->identificacion ?? ''),
            'telefono' => trim($this->telefono ?? ''),
        ]);
    }
}
