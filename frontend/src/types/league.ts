export interface Team {
  id: number
  name: string
  strength: number
}

export interface Fixture {
  id: number
  week: number
  home_team: Team
  away_team: Team
  home_score: number | null
  away_score: number | null
  played_at: string | null
  is_played: boolean
}

export interface WeekFixtures {
  week: number
  fixtures: Fixture[]
}

export interface Standing {
  team: Team
  played: number
  won: number
  drawn: number
  lost: number
  goals_for: number
  goals_against: number
  goal_difference: number
  points: number
}

export interface Prediction {
  team: Team
  percentage: number
}

/** Read endpoints return `{ data }`; action endpoints add a `message`. */
export interface DataResponse<T> {
  data: T
}

export interface ActionResponse<T> {
  message: string
  data: T
}
