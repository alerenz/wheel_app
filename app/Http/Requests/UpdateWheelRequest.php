<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistDayOfWeek;
use App\Enums\StatusWheelType;

class UpdateWheelRequest extends FormRequest
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
            'days_of_week'=>['required',new ExistDayOfWeek()],
            'date_start'=>['required', 'date',],
            'date_end' => ['required', 'date', 'after:today','different:date_start'],
            'status' => ['required', 'in:' . implode(',', array_column(StatusWheelType::cases(), 'value'))],
            
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Наименование обязательно для заполнения',
            'name.string' => 'Тип данных наименование строка',
            'date_start.required' => 'Дата начала обязательна',
            'date_start.date' => 'Неверный формат даты',
            'date_end.required' => 'Дата окончания обязательна',
            'date_end.date' => 'Неверный формат даты',
            'date_end.after' => 'Дата окончания должна быть позже текущей',
            'date_end.different'=>'Дата начала и дата окончания не могут быть одинаковыми',
            'status.required' => 'Статус обязателен для заполнения',
            'status.in'=>'Неверный тип статуса, может быть только: '.implode(', ', array_column(StatusWheelType::cases(), 'value')),
            'days_of_week.required'=>'Дни недели обязательны для заполнения',
        ];
    }
}
