<script setup lang="ts">
import type { Standing } from '@/types/league'

defineProps<{ standing: Standing }>()
</script>

<template>
  <div
    class="relative rounded-2xl bg-gradient-to-r from-amber-400/50 via-amber-300/15 to-emerald-400/30 p-px shadow-[0_0_50px_-12px_rgba(251,191,36,0.35)]"
  >
    <div class="relative overflow-hidden rounded-[15px] bg-slate-900/70 p-5 backdrop-blur-xl">
      <div
        class="pointer-events-none absolute -right-12 -top-16 h-44 w-44 rounded-full bg-amber-400/15 blur-3xl"
        aria-hidden="true"
      />
      <div
        class="pointer-events-none absolute -left-10 bottom-0 h-32 w-32 rounded-full bg-emerald-400/10 blur-3xl"
        aria-hidden="true"
      />

      <div class="relative flex flex-wrap items-center gap-4">
        <div
          class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-300/30 to-amber-500/20 text-amber-200 shadow-lg shadow-amber-500/20 ring-1 ring-amber-300/40"
        >
          <svg
            class="h-8 w-8"
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
          <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-300/90">
            Champions
          </p>
          <p class="text-2xl font-bold tracking-tight text-white">{{ standing.team.name }}</p>
        </div>

        <dl class="flex flex-wrap items-center gap-x-6 gap-y-2 text-center">
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
          <div>
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
  </div>
</template>
