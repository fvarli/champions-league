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

    <ul v-else class="space-y-3">
      <li v-for="(prediction, index) in predictions" :key="prediction.team.id">
        <div class="mb-1 flex items-center justify-between text-sm">
          <span class="flex items-center gap-1.5">
            <span v-if="index === 0" class="text-amber-300" aria-hidden="true">★</span>
            <span :class="index === 0 ? 'font-semibold text-white' : 'text-slate-300'">
              {{ prediction.team.name }}
            </span>
          </span>
          <span class="tabular-nums font-medium text-slate-200">{{ prediction.percentage }}%</span>
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-white/5">
          <div
            class="h-full rounded-full transition-[width] duration-500"
            :class="
              index === 0 ? 'bg-gradient-to-r from-amber-400 to-emerald-400' : 'bg-emerald-500/70'
            "
            :style="{ width: barWidth(prediction.percentage) }"
          />
        </div>
      </li>
    </ul>
  </div>
</template>
