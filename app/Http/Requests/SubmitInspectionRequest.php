<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_condition' => 'required|in:good,needs_cleaning,damaged',
            'damage_found' => 'required|boolean',
            'damage_cost' => 'required_if:damage_found,1|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required|string',
            'items.*.condition' => 'required|in:good,damaged,missing',
            'items.*.charge_amount' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'damage_cost.required_if' => 'Biaya kerusakan wajib diisi jika ditemukan kerusakan.',
            'items.*.item_name.required' => 'Nama item inspeksi wajib diisi.',
        ];
    }
}
