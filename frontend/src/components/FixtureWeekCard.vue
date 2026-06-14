<script setup lang="ts">
import { computed } from 'vue'

import type { Fixture, WeekFixtures } from '@/types/league'

const props = withDefaults(defineProps<{ week: WeekFixtures; current?: boolean }>(), {
  current: false,
})

const emit = defineEmits<{ edit: [fixture: Fixture] }>()

const allPlayed = computed(() => props.week.fixtures.every((fixture) => fixture.is_played))

type Side = 'home' | 'away'

function isWinner(fixture: Fixture, side: Side): boolean {
  if (!fixture.is_played || fixture.home_score === null || fixture.away_score === null) {
    return false
  }

  return side === 'home'
    ? fixture.home_score > fixture.away_score
    : fixture.away_score > fixture.home_score
}
</script>

<template>
  <div
    class="rounded-2xl border bg-slate-900/50 p-4 transition duration-200 hover:-translate-y-0.5"
    :class="
      current
        ? 'border-emerald-500/40 ring-1 ring-emerald-500/20'
        : 'border-white/5 hover:border-white/10'
    "
  >
    <div class="mb-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <h3 class="text-sm font-semibold text-white">Week {{ week.week }}</h3>
        <span
          v-if="current"
          class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-emerald-300"
        >
          Up next
        </span>
      </div>
      <span
        class="rounded-full px-2.5 py-0.5 text-[10px] font-medium uppercase tracking-wide"
        :class="allPlayed ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-500/15 text-slate-400'"
      >
        {{ allPlayed ? 'Played' : 'Pending' }}
      </span>
    </div>

    <ul class="space-y-2">
      <li
        v-for="fixture in week.fixtures"
        :key="fixture.id"
        class="group flex items-center gap-1.5 rounded-xl bg-slate-950/40 px-3 py-2.5"
      >
        <div class="grid flex-1 grid-cols-[1fr_auto_1fr] items-center gap-2">
          <span
            class="truncate text-right text-sm"
            :class="isWinner(fixture, 'home') ? 'font-semibold text-white' : 'text-slate-300'"
          >
            {{ fixture.home_team.name }}
          </span>

          <span
            v-if="fixture.is_played"
            class="min-w-[3rem] rounded-md bg-white/5 px-2 py-1 text-center text-sm font-semibold tabular-nums text-white"
          >
            {{ fixture.home_score }} : {{ fixture.away_score }}
          </span>
          <span
            v-else
            class="min-w-[3rem] rounded-md bg-white/5 px-2 py-1 text-center text-xs font-medium text-slate-400"
          >
            vs
          </span>

          <span
            class="truncate text-left text-sm"
            :class="isWinner(fixture, 'away') ? 'font-semibold text-white' : 'text-slate-300'"
          >
            {{ fixture.away_team.name }}
          </span>
        </div>

        <button
          type="button"
          class="shrink-0 rounded-md p-1 text-slate-500 opacity-0 transition hover:bg-white/5 hover:text-slate-200 focus-visible:opacity-100 group-hover:opacity-100 max-sm:opacity-60"
          :aria-label="`Edit ${fixture.home_team.name} versus ${fixture.away_team.name} result`"
          @click="emit('edit', fixture)"
        >
          <svg
            class="h-3.5 w-3.5"
            viewBox="0 0 20 20"
            fill="none"
            stroke="currentColor"
            stroke-width="1.6"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M13.5 3.5l3 3L7 16l-3.6.6.6-3.6 9.5-9.5Z"
            />
          </svg>
        </button>
      </li>
    </ul>
  </div>
</template>
