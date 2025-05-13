<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromocodesCodeRequest extends FormRequest
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
            'file'=>['required','file','mimes:csv,txt','max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'file.required'=>'Укажите файл',
            'file.file'=>'Это должен быть файл',
            'file.mimes'=>'Файл должен быть формата .csv',
            'file.max'=>'Файл не должен превышать 2 Мб'
        ];
    }
}
