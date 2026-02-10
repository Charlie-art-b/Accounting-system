<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFixedAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'acquisition_value' => 'required|numeric|min:0',
            'acquisition_date' => 'required|date|before_or_equal:today',

            'useful_life_years' => 'required|integer|min:1|max:100',

            'residual_value' => 'required|numeric|min:0|lte:acquisition_value',

            'accumulated_depreciation' => 'required|numeric|min:0',

            'net_value' => 'nullable|numeric|min:0',

            'status' => 'required|in:active,disposed',

            'disposal_date' => 'nullable|date|required_if:status,disposed|after_or_equal:acquisition_date',

            'disposal_reason' => 'nullable|string|required_if:status,disposed|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'asset_name.required' => 'El nombre del activo es obligatorio.',
            'asset_name.max' => 'El nombre del activo no puede superar los 255 caracteres.',

            'description.string' => 'La descripción debe ser un texto válido.',

            'acquisition_value.required' => 'El valor de adquisición es obligatorio.',
            'acquisition_value.numeric' => 'El valor de adquisición debe ser numérico.',
            'acquisition_value.min' => 'El valor de adquisición no puede ser negativo.',

            'acquisition_date.required' => 'La fecha de adquisición es obligatoria.',
            'acquisition_date.date' => 'Debe ingresar una fecha válida.',
            'acquisition_date.before_or_equal' => 'La fecha de adquisición no puede ser futura.',

            'useful_life_years.required' => 'La vida útil es obligatoria.',
            'useful_life_years.integer' => 'La vida útil debe ser un número entero.',
            'useful_life_years.min' => 'La vida útil debe ser mayor a 0.',
            'useful_life_years.max' => 'La vida útil parece demasiado alta.',

            'residual_value.required' => 'El valor residual es obligatorio.',
            'residual_value.numeric' => 'El valor residual debe ser numérico.',
            'residual_value.min' => 'El valor residual no puede ser negativo.',
            'residual_value.lte' => 'El valor residual no puede ser mayor al valor de adquisición.',

            'accumulated_depreciation.required' => 'La depreciación acumulada es obligatoria.',
            'accumulated_depreciation.numeric' => 'La depreciación acumulada debe ser numérica.',
            'accumulated_depreciation.min' => 'La depreciación acumulada no puede ser negativa.',

            'net_value.numeric' => 'El valor neto debe ser numérico.',
            'net_value.min' => 'El valor neto no puede ser negativo.',

            'status.required' => 'Debe indicar el estado del activo.',
            'status.in' => 'El estado seleccionado no es válido.',

            'disposal_date.required_if' => 'Debe indicar la fecha de baja cuando el activo está dado de baja.',
            'disposal_date.date' => 'La fecha de baja debe ser válida.',
            'disposal_date.after_or_equal' => 'La fecha de baja no puede ser anterior a la adquisición.',

            'disposal_reason.required_if' => 'Debe indicar el motivo de baja.',
            'disposal_reason.string' => 'El motivo debe ser texto válido.',
            'disposal_reason.max' => 'El motivo no debe superar los 255 caracteres.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->net_value !== null) {
                $expected = $this->acquisition_value - $this->accumulated_depreciation;

                if (abs($expected - $this->net_value) > 1) {
                    $validator->errors()->add(
                        'net_value',
                        'El valor neto no coincide con el cálculo esperado (valor adquisición - depreciación).'
                    );
                }
            }
        });
    }
}
