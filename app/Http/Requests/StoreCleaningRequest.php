<?php

namespace App\Http\Requests;

use App\Enums\HousekeepingRequestType;
use Illuminate\Foundation\Http\FormRequest;

class StoreCleaningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_id' => 'required|exists:reservations,id',
            'request_type' => 'required|in:stayover_cleaning,deep_cleaning,linen_replacement,maintenance',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'request_type.required' => 'Jenis request wajib dipilih.',
            'priority.required' => 'Level prioritas wajib dipilih.',
        ];
    }
}
