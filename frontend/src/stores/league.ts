import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import { ApiError } from '@/services/http'
import { leagueApi } from '@/services/leagueApi'
import type { Prediction, Standing, Team, WeekFixtures } from '@/types/league'

function toMessage(error: unknown): string {
  if (error instanceof Error) {
    return error.message
  }

  return 'Something went wrong. Please try again.'
}

export const useLeagueStore = defineStore('league', () => {
  const teams = ref<Team[]>([])
  const weeks = ref<WeekFixtures[]>([])
  const standings = ref<Standing[]>([])
  const predictions = ref<Prediction[]>([])
  const predictionNotice = ref<string | null>(null)

  const loading = ref(false)
  const activeAction = ref<string | null>(null)
  const error = ref<string | null>(null)
  const notice = ref<string | null>(null)

  const allFixtures = computed(() => weeks.value.flatMap((week) => week.fixtures))
  const hasFixtures = computed(() => allFixtures.value.length > 0)
  const totalFixtures = computed(() => allFixtures.value.length)
  const playedFixtures = computed(
    () => allFixtures.value.filter((fixture) => fixture.is_played).length,
  )
  const remainingFixtures = computed(() => totalFixtures.value - playedFixtures.value)
  const isComplete = computed(() => totalFixtures.value > 0 && remainingFixtures.value === 0)

  const playableWeeks = computed(() =>
    weeks.value
      .filter((week) => week.fixtures.some((fixture) => !fixture.is_played))
      .map((week) => week.week),
  )
  const currentWeek = computed<number | null>(() => playableWeeks.value[0] ?? null)
  const champion = computed<Standing | null>(() =>
    isComplete.value ? (standings.value[0] ?? null) : null,
  )

  const busy = computed(() => activeAction.value !== null)

  async function loadPredictions(): Promise<void> {
    // Before any fixtures exist a prediction is meaningless; show guidance
    // instead of calling the endpoint.
    if (totalFixtures.value === 0) {
      predictions.value = []
      predictionNotice.value = 'Predictions unlock once the season is underway.'

      return
    }

    try {
      predictions.value = await leagueApi.getPredictions()
      predictionNotice.value = null
    } catch (caught) {
      predictions.value = []
      predictionNotice.value =
        caught instanceof ApiError ? caught.message : 'Predictions are not available right now.'
    }
  }

  async function loadDashboard(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const [loadedTeams, loadedWeeks, loadedStandings] = await Promise.all([
        leagueApi.getTeams(),
        leagueApi.getFixtures(),
        leagueApi.getStandings(),
      ])

      teams.value = loadedTeams
      weeks.value = loadedWeeks
      standings.value = loadedStandings

      await loadPredictions()
    } catch (caught) {
      error.value = toMessage(caught)
    } finally {
      loading.value = false
    }
  }

  async function refresh(): Promise<void> {
    const [loadedWeeks, loadedStandings] = await Promise.all([
      leagueApi.getFixtures(),
      leagueApi.getStandings(),
    ])

    weeks.value = loadedWeeks
    standings.value = loadedStandings

    await loadPredictions()
  }

  async function runAction(
    key: string,
    operation: () => Promise<{ message: string }>,
  ): Promise<void> {
    activeAction.value = key
    error.value = null
    notice.value = null

    try {
      const result = await operation()
      notice.value = result.message
      await refresh()
    } catch (caught) {
      error.value = toMessage(caught)
    } finally {
      activeAction.value = null
    }
  }

  const generate = (): Promise<void> => runAction('generate', () => leagueApi.generate())
  const playWeek = (week: number): Promise<void> =>
    runAction('week', () => leagueApi.playWeek(week))
  const playNext = (): Promise<void> => runAction('next', () => leagueApi.playNext())
  const playAll = (): Promise<void> => runAction('all', () => leagueApi.playAll())

  function dismissError(): void {
    error.value = null
  }

  function dismissNotice(): void {
    notice.value = null
  }

  return {
    teams,
    weeks,
    standings,
    predictions,
    predictionNotice,
    loading,
    activeAction,
    error,
    notice,
    hasFixtures,
    totalFixtures,
    playedFixtures,
    remainingFixtures,
    isComplete,
    playableWeeks,
    currentWeek,
    champion,
    busy,
    loadDashboard,
    generate,
    playWeek,
    playNext,
    playAll,
    dismissError,
    dismissNotice,
  }
})
