<?php

namespace App\Models;

use Database\Factories\FixtureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single league fixture between two teams. Maps to the "matches" table
 * ("Match" is a reserved keyword in PHP and cannot be used as a class name).
 */
class Fixture extends Model
{
    /** @use HasFactory<FixtureFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matches';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'week',
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'played_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week' => 'integer',
            'home_score' => 'integer',
            'away_score' => 'integer',
            'played_at' => 'datetime',
        ];
    }

    /**
     * The team playing at home.
     *
     * @return BelongsTo<Team, $this>
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * The team playing away.
     *
     * @return BelongsTo<Team, $this>
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}
