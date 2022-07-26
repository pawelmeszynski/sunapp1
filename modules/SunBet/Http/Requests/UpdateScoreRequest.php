<?php

namespace SunAppModules\SunBet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScoreRequest extends FormRequest
{
    /**
     * Determine if the users is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules() : array
    {
        return [
            'home_team_goals' => ['required', 'integer'],
            'away_team_goals' => ['required', 'integer'],
        ];
    }
}
