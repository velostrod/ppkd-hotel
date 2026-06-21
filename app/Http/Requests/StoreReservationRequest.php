<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'checkin_date' => 'required|date|after_or_equal:today',
            'checkout_date' => 'required|date|after:checkin_date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'breakfast' => 'nullable|boolean',
            'extra_bed' => 'nullable|boolean',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'payment_type' => 'required|in:full,deposit',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'deposit_amount' => 'required_if:payment_type,deposit|nullable|numeric|min:100000',
        ];
    }

    public function messages(): array
    {
        return [
            'guest_id.required' => 'Pilih tamu terlebih dahulu.',
            'room_id.required' => 'Pilih kamar terlebih dahulu.',
            'checkin_date.after_or_equal' => 'Tanggal check-in tidak boleh di masa lalu.',
            'checkout_date.after' => 'Tanggal checkout harus setelah tanggal check-in.',
            'adults.min' => 'Minimal 1 tamu dewasa.',
        ];
    }
}
