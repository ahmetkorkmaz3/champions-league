<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('teams')->ignore($this->route('team')),
            ],
            'power_level' => 'sometimes|required|integer|min:1|max:100',
            'logo' => 'nullable|string|max:255',
            'city' => 'sometimes|required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Takım adı gereklidir.',
            'name.unique' => 'Bu takım adı zaten kullanılmaktadır.',
            'power_level.required' => 'Güç seviyesi gereklidir.',
            'power_level.min' => 'Güç seviyesi en az 1 olmalıdır.',
            'power_level.max' => 'Güç seviyesi en fazla 100 olmalıdır.',
            'city.required' => 'Şehir adı gereklidir.',
        ];
    }
} 