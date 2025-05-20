<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


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
            'name'=>['required','string']
        ];
    }


    public function messages()
    {
        return [
            'name.required'=>'Наименование промокода обязательно для заполнения',
            'name.string'=>'Наименование должно быть строкой'
        ];
    }

}
