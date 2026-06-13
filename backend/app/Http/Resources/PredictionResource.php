<?php

namespace App\Http\Resources;

use App\Services\ChampionChance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PredictionResource extends JsonResource
{
    public function __construct(private readonly ChampionChance $chance)
    {
        parent::__construct($chance);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'team' => new TeamResource($this->chance->team),
            'percentage' => round($this->chance->percentage, 2),
        ];
    }
}
