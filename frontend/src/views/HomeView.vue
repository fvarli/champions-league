<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import ActionPanel from '@/components/ActionPanel.vue'
import AppShell from '@/components/AppShell.vue'
import ChampionBanner from '@/components/ChampionBanner.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
import EmptyState from '@/components/EmptyState.vue'
import ErrorBanner from '@/components/ErrorBanner.vue'
import FixtureWeekCard from '@/components/FixtureWeekCard.vue'
import MetricCard from '@/components/MetricCard.vue'
import PredictionPanel from '@/components/PredictionPanel.vue'
import SkeletonDashboard from '@/components/SkeletonDashboard.vue'
import StandingsTable from '@/components/StandingsTable.vue'
import ToastHost from '@/components/ToastHost.vue'
import { useLeagueStore } from '@/stores/league'

const store = useLeagueStore()

onMounted(() => store.loadDashboard())

const confirmPlayAll = ref(false)
const playingAll = computed(() => store.activeAction === 'all')

async function onConfirmPlayAll(): Promise<void> {
  await store.playAll()
  confirmPlayAll.value = false
}

const confirmReset = ref(false)
const resetting = computed(() => store.activeAction === 'reset')

async function onConfirmReset(): Promise<void> {
  await store.reset()
  confirmReset.value = false
}

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

    <ToastHost />

    <Transition name="fade" mode="out-in">
      <SkeletonDashboard v-if="store.loading" key="skeleton" />

      <div v-else key="content" class="space-y-6">
        <ErrorBanner :message="store.error" @dismiss="store.dismissError" />

        <Transition name="pop">
          <ChampionBanner v-if="store.champion" :standing="store.champion" />
        </Transition>

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
          @play-all="confirmPlayAll = true"
          @reset="confirmReset = true"
        />

        <div class="grid gap-6 lg:grid-cols-3">
          <div class="space-y-6 lg:col-span-2">
            <StandingsTable :standings="store.standings" :complete="store.isComplete" />

            <section>
              <h2 class="mb-3 text-sm font-semibold text-white">Fixtures</h2>

              <EmptyState
                v-if="!store.hasFixtures"
                title="No fixtures yet"
                message="Generate the schedule to kick off the season — twelve matches across six weeks."
                icon="pitch"
              />

              <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <FixtureWeekCard
                  v-for="week in store.weeks"
                  :key="week.week"
                  :week="week"
                  :current="week.week === store.currentWeek"
                />
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
    </Transition>

    <ConfirmModal
      :open="confirmPlayAll"
      title="Play all remaining fixtures?"
      message="This simulates every unplayed match through to the end of the season. It can't be undone."
      confirm-label="Play All"
      :busy="playingAll"
      @cancel="confirmPlayAll = false"
      @confirm="onConfirmPlayAll"
    />

    <ConfirmModal
      :open="confirmReset"
      title="Reset the season?"
      message="This clears all fixtures and standings and keeps the four teams, giving you a clean slate."
      confirm-label="Reset Season"
      variant="danger"
      :busy="resetting"
      @cancel="confirmReset = false"
      @confirm="onConfirmReset"
    />
  </AppShell>
</template>
