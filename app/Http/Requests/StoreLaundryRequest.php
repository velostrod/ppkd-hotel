<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaundryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_id' => 'required|exists:reservations,id',
            'notes' => 'required|string',
            'total_charge' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required' => 'Deskripsi item laundry wajib diisi.',
            'total_charge.required' => 'Total biaya laundry wajib diisi.',
        ];
    }
}
