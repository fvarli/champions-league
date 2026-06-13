<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'strength',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'strength' => 'integer',
        ];
    }

    /**
     * Matches in which this team plays at home.
     *
     * @return HasMany<Fixture, $this>
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    /**
     * Matches in which this team plays away.
     *
     * @return HasMany<Fixture, $this>
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }
}
