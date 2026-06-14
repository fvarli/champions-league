<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFixtureScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0', 'max:5'],
            'away_score' => ['required', 'integer', 'min:0', 'max:5'],
        ];
    }
}
