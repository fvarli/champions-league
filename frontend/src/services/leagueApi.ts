import { request } from './http'
import type {
  ActionResponse,
  DataResponse,
  Fixture,
  Prediction,
  Standing,
  Team,
  WeekFixtures,
} from '@/types/league'

/** Turn the API's `{ "1": [...], "2": [...] }` shape into a sorted list. */
function toWeeks(grouped: Record<string, Fixture[]>): WeekFixtures[] {
  return Object.entries(grouped)
    .map(([week, fixtures]) => ({ week: Number(week), fixtures }))
    .sort((a, b) => a.week - b.week)
}

export const leagueApi = {
  getTeams: (): Promise<Team[]> => request<DataResponse<Team[]>>('/teams').then((r) => r.data),

  getStandings: (): Promise<Standing[]> =>
    request<DataResponse<Standing[]>>('/standings').then((r) => r.data),

  getPredictions: (): Promise<Prediction[]> =>
    request<DataResponse<Prediction[]>>('/predictions').then((r) => r.data),

  getFixtures: (): Promise<WeekFixtures[]> =>
    request<DataResponse<Record<string, Fixture[]>>>('/fixtures').then((r) => toWeeks(r.data)),

  generate: (): Promise<ActionResponse<Fixture[]>> =>
    request<ActionResponse<Fixture[]>>('/fixtures/generate', { method: 'POST' }),

  playWeek: (week: number): Promise<ActionResponse<Fixture[]>> =>
    request<ActionResponse<Fixture[]>>(`/weeks/${week}/play`, { method: 'POST' }),

  playNext: (): Promise<ActionResponse<Fixture[]>> =>
    request<ActionResponse<Fixture[]>>('/weeks/next/play', { method: 'POST' }),

  playAll: (): Promise<ActionResponse<Record<string, Fixture[]>>> =>
    request<ActionResponse<Record<string, Fixture[]>>>('/league/play-all', { method: 'POST' }),

  reset: (): Promise<
    ActionResponse<{ teams: Team[]; fixtures: Fixture[]; standings: Standing[] }>
  > =>
    request<ActionResponse<{ teams: Team[]; fixtures: Fixture[]; standings: Standing[] }>>(
      '/league/reset',
      { method: 'POST' },
    ),
}
