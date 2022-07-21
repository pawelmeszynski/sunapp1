<?php

namespace SunAppModules\SunBet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScoreRequest extends FormRequest
{
    /**
     * Determine if the users is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'home_team_goals' => ['integer'],
            'match_id' => ['integer'],
            'user_id' => ['integer'],
            'away_team_goals' => ['integer'],
        ];
    }
}
