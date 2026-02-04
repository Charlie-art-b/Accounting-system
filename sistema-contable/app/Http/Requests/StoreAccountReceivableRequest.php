<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountReceivableRequest extends FormRequest
{
    
    //Autoriza la solicitud
    
    public function authorize(): bool
    {
        return true;
    }

    //reglas de validación para registrar cuentas por cobrar
    
    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accounts_receivable', 'invoice_number')
                    ->where(fn ($q) => $q->where('customer_id', $this->input('customer_id'))),
            ],

            'issue_date' => [
                'required',
                'date',
            ],

            'due_date' => [
                'required',
                'date',
                'after_or_equal:issue_date',
            ],

            'description' => [
                'required',
                'string',
                'max:255',
            ],

            'total_amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'paid_amount' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }

    //mensajes de error
     
    public function messages(): array
    {
        return [
            'customer_id.required' => 'El cliente es obligatorio.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',

            'invoice_number.required' => 'La factura es obligatoria.',
            'invoice_number.unique' =>
                'Ya existe una cuenta por cobrar con esta factura para el cliente seleccionado.',
            'invoice_number.max' => 'La factura no puede exceder 50 caracteres.',

            'issue_date.required' => 'La fecha de emisión es obligatoria.',
            'issue_date.date' => 'La fecha de emisión no es válida.',

            'due_date.required' => 'La fecha de vencimiento es obligatoria.',
            'due_date.after_or_equal' =>
                'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',

            'description.required' => 'La descripción es obligatoria.',
            'description.max' => 'La descripción no puede exceder 255 caracteres.',

            'total_amount.required' => 'El monto total es obligatorio.',
            'total_amount.gt' => 'El monto total debe ser mayor a cero.',

            'paid_amount.prohibited' =>
                'El monto pagado se inicializa automáticamente.',
            'status.prohibited' =>
                'El estado se define automáticamente por el sistema.',
        ];
    }
}
