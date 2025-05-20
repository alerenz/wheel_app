<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistIdPrize;

class UpdateSectorRequest extends FormRequest
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
            'prize_type' => ['required'],
            'prize_id' => [
                'required',
                new ExistIdPrize($prizeType),
            ],
            'probability' => ['numeric', 'min:0'],
            'wheel_id' => ['required', 'exists:wheels,id'],

        ];
    }


    public function messages(){
        return [
            'prize_type.required' => 'Тип приза обязателен для заполнения',
            'prize_id.required' => 'ID приза обязателен для заполнения',
            'probability.numeric'=>'Вероятность должна быть числом',
            'probability.min'=>'Вероятность не может быть отрицательной',
            'wheel_id.required'=>'ID колеса обязателен для заполнения',
            'wheel_id.exists'=>'Колеса с таким id не существует',

        ];
    }
}
