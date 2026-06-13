<?php

namespace App\Http\Resources;

use App\Services\TeamStanding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandingResource extends JsonResource
{
    public function __construct(private readonly TeamStanding $standing)
    {
        parent::__construct($standing);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'team' => new TeamResource($this->standing->team),
            'played' => $this->standing->played,
            'won' => $this->standing->won,
            'drawn' => $this->standing->drawn,
            'lost' => $this->standing->lost,
            'goals_for' => $this->standing->goalsFor,
            'goals_against' => $this->standing->goalsAgainst,
            'goal_difference' => $this->standing->goalDifference(),
            'points' => $this->standing->points(),
        ];
    }
}
