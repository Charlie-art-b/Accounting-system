<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFixedAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',

            'acquisition_value' => 'sometimes|required|numeric|min:0',
            'acquisition_date' => 'sometimes|required|date|before_or_equal:today',

            'useful_life_years' => 'sometimes|required|integer|min:1|max:100',

            'residual_value' => 'sometimes|required|numeric|min:0|lte:acquisition_value',

            'accumulated_depreciation' => 'sometimes|required|numeric|min:0',

            'net_value' => 'nullable|numeric|min:0',

            'status' => 'sometimes|required|in:active,disposed',

            'disposal_date' => 'nullable|date|required_if:status,disposed|after_or_equal:acquisition_date',

            'disposal_reason' => 'nullable|string|required_if:status,disposed|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'asset_name.required' => 'El nombre del activo es obligatorio.',
            'asset_name.max' => 'El nombre no debe superar los 255 caracteres.',

            'acquisition_value.numeric' => 'El valor de adquisición debe ser numérico.',
            'acquisition_value.min' => 'El valor de adquisición no puede ser negativo.',

            'acquisition_date.date' => 'Debe ingresar una fecha válida.',
            'acquisition_date.before_or_equal' => 'La fecha no puede ser futura.',

            'useful_life_years.integer' => 'La vida útil debe ser un número entero.',
            'useful_life_years.min' => 'La vida útil debe ser mayor a 0.',

            'residual_value.numeric' => 'El valor residual debe ser numérico.',
            'residual_value.lte' => 'El valor residual no puede superar el valor de adquisición.',

            'accumulated_depreciation.numeric' => 'La depreciación debe ser numérica.',
            'accumulated_depreciation.min' => 'La depreciación no puede ser negativa.',

            'net_value.numeric' => 'El valor neto debe ser numérico.',

            'status.in' => 'Estado inválido.',

            'disposal_date.required_if' => 'Debe indicar la fecha de baja si el activo está dado de baja.',
            'disposal_date.after_or_equal' => 'La fecha de baja no puede ser anterior a la adquisición.',

            'disposal_reason.required_if' => 'Debe indicar el motivo de baja.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->net_value !== null && $this->acquisition_value !== null && $this->accumulated_depreciation !== null) {
                $expected = $this->acquisition_value - $this->accumulated_depreciation;

                if (abs($expected - $this->net_value) > 1) {
                    $validator->errors()->add(
                        'net_value',
                        'El valor neto no coincide con el cálculo esperado.'
                    );
                }
            }
        });
    }
}
