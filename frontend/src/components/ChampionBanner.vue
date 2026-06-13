<script setup lang="ts">
import type { Standing } from '@/types/league'

defineProps<{ standing: Standing }>()
</script>

<template>
  <div
    class="relative overflow-hidden rounded-2xl border border-amber-400/30 bg-gradient-to-r from-amber-500/15 via-amber-400/5 to-emerald-500/10 p-5"
  >
    <div
      class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-amber-400/10 blur-2xl"
      aria-hidden="true"
    />

    <div class="relative flex flex-wrap items-center gap-4">
      <div
        class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-400/20 text-amber-300 ring-1 ring-amber-400/40"
      >
        <svg
          class="h-7 w-7"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.6"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M8 21h8m-4-4v4m-5-15h10v3a5 5 0 01-10 0V6Z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M5 6H3v1a3 3 0 003 3m13-4h2v1a3 3 0 01-3 3"
          />
        </svg>
      </div>

      <div class="mr-auto">
        <p class="text-xs font-medium uppercase tracking-wide text-amber-300/80">Champions</p>
        <p class="text-xl font-semibold text-white">{{ standing.team.name }}</p>
      </div>

      <dl class="flex items-center gap-5 text-center">
        <div>
          <dt class="text-xs uppercase tracking-wide text-slate-400">Points</dt>
          <dd class="text-lg font-semibold text-white">{{ standing.points }}</dd>
        </div>
        <div>
          <dt class="text-xs uppercase tracking-wide text-slate-400">Record</dt>
          <dd class="text-lg font-semibold text-white">
            {{ standing.won }}–{{ standing.drawn }}–{{ standing.lost }}
          </dd>
        </div>
        <div class="hidden sm:block">
          <dt class="text-xs uppercase tracking-wide text-slate-400">GD</dt>
          <dd class="text-lg font-semibold text-white">
            {{
              standing.goal_difference > 0
                ? `+${standing.goal_difference}`
                : standing.goal_difference
            }}
          </dd>
        </div>
      </dl>
    </div>
  </div>
</template>
