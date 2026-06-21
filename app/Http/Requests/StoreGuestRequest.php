<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'id_number' => 'required|string|max:50',
            'nationality' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'id_number.required' => 'Nomor identitas wajib diisi.',
            'nationality.required' => 'Kewarganegaraan wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
        ];
    }
}
