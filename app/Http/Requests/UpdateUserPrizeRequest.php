<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistIdPrize;

class UpdateUserPrizeRequest extends FormRequest
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
        $prizeType = $this->input('prize_type');
        return [
            'date' => ['required', 'date', 'equal:today'],
            'prize_type' => ['required'],
            'prize_id' => [
                'required',
                new ExistIdPrize($prizeType),
            ],
        ];
    }

    public function messages(){
        return [
            'prize_type.required' => 'Тип приза обязателен для заполнения',
            'prize_id.required' => 'ID приза обязателен для заполнения',
            'date.required' => 'Дата истечения срока действия обязательна',
            'date.date' => 'Неверный формат даты',
            'date.equal' => 'Дата должна быть текущей',
        ];
    }
}
