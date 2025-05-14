<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'username' => ['required', 'string', 'unique:users,username','min:5'],
            'password' => ['required','string','min:6', 'max:20'],
            'surname'=> ['required','string'],
            'name'=> ['required','string'],
            'patronymic'=>['required','string'],
        ];
    }

    public function messages(){
        return [
            'username.required' => 'Логин обязателен для заполнения',
            'username.string' => 'Логин должен быть строкой',
            'username.unique' => 'Такой логин уже есть',
            'username.min' => 'Длина логина должна быть не меньше 5 символов',
            'password.required' => 'Пароль обязателен для заполнения',
            'password.string' => 'Пароль должен быть строкой',
            'password.min' => 'Длина пароля должна быть не меньше 6 символов',
            'password.max' => 'Длина пароля должна быть не больше 20 символов',
            'surname.required' => 'Фамилия обязательна для заполнения',
            'surname.string' => 'Фамилия должна быть строкой',
            'name.required' => 'Имя обязательно для заполнения',
            'name.string' => 'Имя должно быть строкой',
            'patronymic.required' => 'Отчевство обязательно для заполнения',
            'patronymic.string' => 'Отчевство должно быть строкой',
        ];
    }
}
