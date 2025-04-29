<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistDayOfWeek;
use App\Enums\StatusWeelType;

class StoreWheelRequest extends FormRequest
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
            'name'=>['required','string'],
            'count_sectors' => ['required', 'numeric', 'min:4', 'max:10'],
            'days_of_week'=>['required',new ExistDayOfWeek()],
            'date_start'=>['required', 'date',],
            'date_end' => ['required', 'date', 'after:today'],
            'status' => ['in:' . implode(',', array_column(StatusWeelType::cases(), 'value'))],
            'animation'=>['boolean'],
            
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Наименование обязательно для заполнения',
            'name.string' => 'Тип данных наименование строка',
            'count_sectors.required' => 'Количество секторов обязательно для заполнения',
            'count_sectors.numeric' => 'Количество секторов должно быть числом',
            'count_sectors.min' => 'Количество секторов не может быть меньше 4',
            'count_sectors.max'=>'Количество секторов не может быть больше 10',
            'date_start.required' => 'Дата начала обязательна',
            'date_start.date' => 'Неверный формат даты',
            'date_end.required' => 'Дата окончания обязательна',
            'date_end.date' => 'Неверный формат даты',
            'date_end.after' => 'Дата окончания должна быть позже текущей',
            'status.required' => 'Статус обязателен для заполнения',
            'status.in'=>'Неверный тип статуса, может быть только: '.implode(', ', array_column(StatusWeelType::cases(), 'value')),
            'animation.boolean'=>"Анимация должна быть булевым значением",
            'days_of_week.required'=>'Дни недели обязательны для заполнения',
        ];
    }
}
