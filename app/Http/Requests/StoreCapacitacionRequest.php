<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCapacitacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('capacitacion') && $this->filled('nombre')) {
            $this->merge([
                'capacitacion' => $this->input('nombre'),
            ]);
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'capacitacion' => ['required', 'string', 'max:150'],
            'fecha' => ['required', 'date'],
            'duracion' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'capacitacion.required' => 'El nombre de la capacitación es obligatorio.',
            'capacitacion.string' => 'El nombre de la capacitación debe ser un texto válido.',
            'capacitacion.max' => 'El nombre de la capacitación no puede exceder los 150 caracteres.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'Selecciona una fecha válida en el calendario.',
            'duracion.required' => 'La duración es obligatoria.',
            'duracion.string' => 'La duración debe ser un texto válido.',
            'duracion.max' => 'La duración no puede exceder los 100 caracteres.',
        ];
    }
}
