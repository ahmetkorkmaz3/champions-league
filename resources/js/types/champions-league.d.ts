export interface Team {
  id: number
  name: string
  city: string
  logo?: string
}

export interface Match {
  id: number
  home_team: Team
  away_team: Team
  home_score: number | null
  away_score: number | null
  is_played: boolean
  week: number
}

export interface Standing {
  id: number
  position: number
  team: Team
  wins: number
  draws: number
  losses: number
  goals_for: number
  goals_against: number
  goal_difference: number
  points: number
}

export interface MatchForm {
  home_score: number
  away_score: number
}

export interface TeamWithPower extends Team {
  power_level: number
}

export interface PredictedStanding extends Standing {
  matches_played: number
  team: TeamWithPower
} 