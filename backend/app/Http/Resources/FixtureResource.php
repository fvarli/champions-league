<?php

namespace App\Http\Resources;

use App\Models\Fixture;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixtureResource extends JsonResource
{
    public function __construct(private readonly Fixture $fixture)
    {
        parent::__construct($fixture);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->fixture->id,
            'week' => $this->fixture->week,
            'home_team' => new TeamResource($this->fixture->homeTeam),
            'away_team' => new TeamResource($this->fixture->awayTeam),
            'home_score' => $this->fixture->home_score,
            'away_score' => $this->fixture->away_score,
            'played_at' => $this->fixture->played_at,
            'is_played' => $this->fixture->played_at !== null,
        ];
    }
}
