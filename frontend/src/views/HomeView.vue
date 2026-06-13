<script setup lang="ts">
import { computed, onMounted } from 'vue'

import ActionPanel from '@/components/ActionPanel.vue'
import AppShell from '@/components/AppShell.vue'
import EmptyState from '@/components/EmptyState.vue'
import ErrorBanner from '@/components/ErrorBanner.vue'
import FixtureWeekCard from '@/components/FixtureWeekCard.vue'
import LoadingState from '@/components/LoadingState.vue'
import MetricCard from '@/components/MetricCard.vue'
import PredictionPanel from '@/components/PredictionPanel.vue'
import StandingsTable from '@/components/StandingsTable.vue'
import { useLeagueStore } from '@/stores/league'

const store = useLeagueStore()

onMounted(() => store.loadDashboard())

const statusLabel = computed(() => {
  if (!store.hasFixtures) {
    return 'Not started'
  }

  if (store.isComplete) {
    return 'Complete'
  }

  return store.currentWeek !== null ? `Week ${store.currentWeek}` : '—'
})
</script>

<template>
  <AppShell>
    <template #actions>
      <div
        v-if="store.champion"
        class="hidden items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1.5 text-sm sm:flex"
      >
        <span class="text-amber-300" aria-hidden="true">★</span>
        <span class="text-slate-300">Champion</span>
        <span class="font-semibold text-white">{{ store.champion.team.name }}</span>
      </div>
    </template>

    <LoadingState v-if="store.loading" label="Loading league…" />

    <div v-else class="space-y-6">
      <ErrorBanner :message="store.error" @dismiss="store.dismissError" />

      <div
        v-if="store.notice"
        class="flex items-start justify-between gap-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"
        role="status"
      >
        <span>{{ store.notice }}</span>
        <button
          type="button"
          class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium text-emerald-200/80 transition hover:bg-emerald-500/20 hover:text-emerald-100"
          @click="store.dismissNotice"
        >
          Dismiss
        </button>
      </div>

      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <MetricCard label="Teams" :value="store.teams.length" accent="sky" />
        <MetricCard
          label="Fixtures played"
          :value="`${store.playedFixtures} / ${store.totalFixtures}`"
          accent="emerald"
        />
        <MetricCard
          label="Remaining"
          :value="store.remainingFixtures"
          hint="fixtures"
          accent="amber"
        />
        <MetricCard label="Status" :value="statusLabel" accent="slate" />
      </div>

      <ActionPanel
        :has-fixtures="store.hasFixtures"
        :playable-weeks="store.playableWeeks"
        :is-complete="store.isComplete"
        :active-action="store.activeAction"
        @generate="store.generate"
        @play-week="store.playWeek"
        @play-next="store.playNext"
        @play-all="store.playAll"
      />

      <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
          <StandingsTable :standings="store.standings" />

          <section>
            <h2 class="mb-3 text-sm font-semibold text-white">Fixtures</h2>

            <EmptyState
              v-if="!store.hasFixtures"
              title="No fixtures yet"
              message="Generate the schedule to kick off the season — twelve matches across six weeks."
            />

            <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
              <FixtureWeekCard v-for="week in store.weeks" :key="week.week" :week="week" />
            </div>
          </section>
        </div>

        <div class="space-y-6">
          <PredictionPanel
            :predictions="store.predictions"
            :notice="store.predictionNotice"
            :is-complete="store.isComplete"
          />
        </div>
      </div>
    </div>
  </AppShell>
</template>
