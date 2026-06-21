<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => [
                'required', 'numeric', 'min:1',
                function ($attribute, $value, $fail) {
                    $reservation = $this->route('reservation');
                    $invoice = $reservation?->invoice;
                    if ($invoice && $value > $invoice->balance_due) {
                        $fail('Jumlah pembayaran melebihi sisa tagihan (Rp ' . number_format($invoice->balance_due, 0, ',', '.') . ').');
                    }
                },
            ],
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Jumlah pembayaran minimal Rp 1.',
            'payment_method_id.required' => 'Pilih metode pembayaran.',
        ];
    }
}
