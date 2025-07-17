<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameMatchRequest extends FormRequest
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
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'week' => 'required|integer|min:1|max:10',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
            'is_played' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'home_team_id.required' => 'Ev sahibi takım gereklidir.',
            'home_team_id.exists' => 'Ev sahibi takım bulunamadı.',
            'away_team_id.required' => 'Deplasman takımı gereklidir.',
            'away_team_id.exists' => 'Deplasman takımı bulunamadı.',
            'away_team_id.different' => 'Ev sahibi ve deplasman takımları farklı olmalıdır.',
            'week.required' => 'Hafta numarası gereklidir.',
            'week.min' => 'Hafta numarası en az 1 olmalıdır.',
            'week.max' => 'Hafta numarası en fazla 10 olmalıdır.',
            'home_score.min' => 'Ev sahibi skoru en az 0 olmalıdır.',
            'away_score.min' => 'Deplasman skoru en az 0 olmalıdır.',
        ];
    }
} 