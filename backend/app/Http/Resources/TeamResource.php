<?php

namespace App\Http\Resources;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function __construct(private readonly Team $team)
    {
        parent::__construct($team);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->team->id,
            'name' => $this->team->name,
            'strength' => $this->team->strength,
        ];
    }
}
