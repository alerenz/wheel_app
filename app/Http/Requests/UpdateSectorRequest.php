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
            'name' => ['required', 'string'],
            'prize_type' => ['required'],
            'prize_id' => [
                'required',
                new ExistIdPrize($prizeType),
            ],
            'probability' => ['numeric', 'min:0', 'max:100'],
            'wheel_id' => ['required', 'exists:wheels,id'],
            'count'=>['required','integer','min:0']
        ];
    }


    public function messages(){
        return [
            'name.required'=>'Наименование сектора обязательно.',
            'name.string'=>'Наименование должно быть строкой.',
            'prize_type.required' => 'Тип приза обязателен для заполнения',
            'prize_id.required' => 'ID приза обязателен для заполнения',
            'probability.numeric'=>'Вероятность должна быть числом',
            'probability.min'=>'Вероятность не может быть отрицательной',
            'probability.max'=>'Вероятность не должна быть больше 100%',
            'wheel_id.required'=>'ID колеса обязателен для заполнения',
            'wheel_id.exists'=>'Колеса с таким id не существует',
            'count.required'=>'Количество призов должно быть указано',
            'count.integer'=>'Количество призов должно быть целым числом',
            'count.min'=>'Количество призов не может быть отрицательным',
        ];
    }
}
