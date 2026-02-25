<?php

namespace App\Http\Requests;

use App\Models\Request as RepairRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'client_name.required' => 'Укажите имя клиента',
            'client_name.max' => 'Имя клиента не более 255 символов',
            'phone.required' => 'Укажите телефон',
            'phone.max' => 'Телефон не более 50 символов',
            'address.required' => 'Укажите адрес',
            'address.max' => 'Адрес не более 500 символов',
            'problem_text.required' => 'Опишите проблему',
        ];
    }

    public function attributes(): array
    {
        return [
            'client_name' => 'имя клиента',
            'phone' => 'телефон',
            'address' => 'адрес',
            'problem_text' => 'описание проблемы',
        ];
    }

    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'problem_text' => ['required', 'string'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', [
                RepairRequest::STATUS_NEW,
                RepairRequest::STATUS_ASSIGNED,
                RepairRequest::STATUS_IN_PROGRESS,
                RepairRequest::STATUS_DONE,
                RepairRequest::STATUS_CANCELED,
            ])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('status')) {
            $this->merge(['status' => RepairRequest::STATUS_NEW]);
        }
    }
}
