<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeagueStandingRequest extends FormRequest
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
            'team_id' => 'required|exists:teams,id|unique:league_standings',
            'points' => 'required|integer|min:0',
            'goals_for' => 'required|integer|min:0',
            'goals_against' => 'required|integer|min:0',
            'goal_difference' => 'required|integer',
            'wins' => 'required|integer|min:0',
            'draws' => 'required|integer|min:0',
            'losses' => 'required|integer|min:0',
            'position' => 'required|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'team_id.required' => 'Takım ID gereklidir.',
            'team_id.exists' => 'Takım bulunamadı.',
            'team_id.unique' => 'Bu takım için zaten lig durumu mevcuttur.',
            'points.required' => 'Puan gereklidir.',
            'points.min' => 'Puan en az 0 olmalıdır.',
            'goals_for.required' => 'Atılan gol sayısı gereklidir.',
            'goals_for.min' => 'Atılan gol sayısı en az 0 olmalıdır.',
            'goals_against.required' => 'Yenen gol sayısı gereklidir.',
            'goals_against.min' => 'Yenen gol sayısı en az 0 olmalıdır.',
            'goal_difference.required' => 'Gol farkı gereklidir.',
            'wins.required' => 'Galibiyet sayısı gereklidir.',
            'wins.min' => 'Galibiyet sayısı en az 0 olmalıdır.',
            'draws.required' => 'Beraberlik sayısı gereklidir.',
            'draws.min' => 'Beraberlik sayısı en az 0 olmalıdır.',
            'losses.required' => 'Mağlubiyet sayısı gereklidir.',
            'losses.min' => 'Mağlubiyet sayısı en az 0 olmalıdır.',
            'position.required' => 'Sıra pozisyonu gereklidir.',
            'position.min' => 'Sıra pozisyonu en az 1 olmalıdır.',
        ];
    }
}
