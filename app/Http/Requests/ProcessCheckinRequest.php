<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $reservation = $this->route('reservation');
        $invoice = $reservation ? $reservation->invoice : null;
        $roomBalance = $invoice ? (float) $invoice->balance_due : 0;

        return [
            'room_payment_method_id'    => $roomBalance > 0 ? 'required|exists:payment_methods,id' : 'nullable|exists:payment_methods,id',
            'deposit_payment_method_id' => 'required|exists:payment_methods,id',
            'deposit_amount'            => 'required|numeric|min:1',
            'notes'                     => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'room_payment_method_id.required'    => 'Metode pembayaran untuk sisa tagihan kamar wajib diisi.',
            'deposit_payment_method_id.required' => 'Metode pembayaran deposit jaminan wajib diisi.',
            'deposit_amount.required'            => 'Jumlah deposit jaminan wajib diisi.',
            'deposit_amount.min'                 => 'Jumlah deposit minimal Rp 1.',
        ];
    }
}
