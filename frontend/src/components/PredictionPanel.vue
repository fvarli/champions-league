<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue'
import type { Prediction } from '@/types/league'

defineProps<{
  predictions: Prediction[]
  notice: string | null
  isComplete: boolean
}>()

function barWidth(percentage: number): string {
  return `${Math.max(2, Math.min(100, percentage))}%`
}
</script>

<template>
  <div class="rounded-2xl border border-white/5 bg-slate-900/50 p-5">
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h2 class="text-sm font-semibold text-white">Championship Prediction</h2>
        <p class="text-xs text-slate-400">Chance of finishing first</p>
      </div>
      <span
        v-if="isComplete"
        class="rounded-full bg-amber-400/15 px-2.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-amber-300"
      >
        Final
      </span>
    </div>

    <EmptyState v-if="notice" title="Not available yet" :message="notice" icon="chart" />

    <ul v-else class="space-y-2">
      <li
        v-for="(prediction, index) in predictions"
        :key="prediction.team.id"
        class="rounded-xl p-2 transition-colors"
        :class="index === 0 ? 'bg-amber-400/[0.06] ring-1 ring-amber-400/15' : ''"
      >
        <div class="mb-1.5 flex items-center justify-between text-sm">
          <span class="flex items-center gap-1.5">
            <svg
              v-if="index === 0"
              class="h-3.5 w-3.5 text-amber-300"
              viewBox="0 0 24 24"
              fill="currentColor"
              aria-hidden="true"
            >
              <path d="M5 16 3 5l5.5 4L12 4l3.5 5L21 5l-2 11H5Zm0 2h14v2H5v-2Z" />
            </svg>
            <span :class="index === 0 ? 'font-semibold text-white' : 'text-slate-300'">
              {{ prediction.team.name }}
            </span>
          </span>
          <span
            class="font-semibold tabular-nums"
            :class="index === 0 ? 'text-amber-200' : 'text-slate-300'"
          >
            {{ prediction.percentage }}%
          </span>
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-white/[0.04] shadow-inner shadow-black/20">
          <div
            class="h-full rounded-full transition-[width] duration-700 ease-out"
            :class="
              index === 0
                ? 'bg-gradient-to-r from-amber-400 via-amber-300 to-emerald-400 shadow-[0_0_10px_rgba(251,191,36,0.5)]'
                : 'bg-gradient-to-r from-emerald-500/80 to-emerald-400/80'
            "
            :style="{ width: barWidth(prediction.percentage) }"
          />
        </div>
      </li>
    </ul>
  </div>
</template>
