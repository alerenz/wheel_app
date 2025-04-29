<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DiscountType;

class StorePromocodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type_discount' => ['required', 'in:' . implode(',', array_column(DiscountType::cases(), 'value'))],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['required', 'date', 'after:today'],
        ];
    }


    public function messages()
    {
        return [
            'type_discount.required' => 'Тип скидки обязателен для заполнения',
            'type_discount.in' => 'Неверный тип скидки',
            'discount_value.required' => 'Значение скидки обязательно для заполнения',
            'discount_value.numeric' => 'Значение скидки должно быть числом',
            'discount_value.min' => 'Значение скидки не может быть отрицательным',
            'expiry_date.required' => 'Дата истечения срока действия обязательна',
            'expiry_date.date' => 'Неверный формат даты',
            'expiry_date.after' => 'Дата должна быть позже текущей',
        ];
    }

}
