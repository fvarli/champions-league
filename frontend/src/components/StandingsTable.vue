<script setup lang="ts">
import type { Standing } from '@/types/league'

const props = withDefaults(defineProps<{ standings: Standing[]; complete?: boolean }>(), {
  complete: false,
})

function isChampion(index: number): boolean {
  return index === 0 && props.complete
}

function formatGoalDifference(value: number): string {
  return value > 0 ? `+${value}` : `${value}`
}
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-white/5 bg-slate-900/50">
    <div class="flex items-center justify-between border-b border-white/5 px-5 py-4">
      <h2 class="text-sm font-semibold text-white">Standings</h2>
      <span class="text-xs text-slate-400">{{ standings.length }} teams</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full min-w-[34rem] text-sm">
        <caption class="sr-only">
          League standings, ordered by points
        </caption>
        <thead>
          <tr class="text-xs uppercase tracking-wide text-slate-400">
            <th scope="col" class="px-4 py-3 text-left font-medium">#</th>
            <th scope="col" class="px-4 py-3 text-left font-medium">Team</th>
            <th scope="col" class="px-2 py-3 text-center font-medium">P</th>
            <th scope="col" class="px-2 py-3 text-center font-medium">W</th>
            <th scope="col" class="px-2 py-3 text-center font-medium">D</th>
            <th scope="col" class="px-2 py-3 text-center font-medium">L</th>
            <th scope="col" class="px-2 py-3 text-center font-medium">GF</th>
            <th scope="col" class="px-2 py-3 text-center font-medium">GA</th>
            <th scope="col" class="px-2 py-3 text-center font-medium">GD</th>
            <th scope="col" class="px-4 py-3 text-center font-medium">Pts</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, index) in standings"
            :key="row.team.id"
            class="border-t border-white/5 transition-colors"
            :class="
              isChampion(index)
                ? 'bg-gradient-to-r from-amber-400/10 to-transparent'
                : index === 0
                  ? 'bg-emerald-500/[0.07]'
                  : 'hover:bg-white/[0.02]'
            "
          >
            <td class="px-4 py-3">
              <span
                v-if="isChampion(index)"
                class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-amber-400/20 text-amber-300"
                aria-hidden="true"
              >
                <svg
                  class="h-4 w-4"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.6"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8 21h8m-4-4v4m-5-15h10v3a5 5 0 01-10 0V6Z"
                  />
                </svg>
              </span>
              <span
                v-else
                class="inline-flex h-6 w-6 items-center justify-center rounded-md text-xs font-semibold"
                :class="
                  index === 0 ? 'bg-amber-400/20 text-amber-300' : 'bg-white/5 text-slate-400'
                "
              >
                {{ index + 1 }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <span class="font-medium text-white">{{ row.team.name }}</span>
                <span
                  v-if="isChampion(index)"
                  class="rounded-full bg-amber-400/15 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-amber-300"
                >
                  Champion
                </span>
                <span
                  v-else-if="index === 0"
                  class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-emerald-300"
                >
                  Leader
                </span>
              </div>
            </td>
            <td class="px-2 py-3 text-center text-slate-300">{{ row.played }}</td>
            <td class="px-2 py-3 text-center text-slate-300">{{ row.won }}</td>
            <td class="px-2 py-3 text-center text-slate-300">{{ row.drawn }}</td>
            <td class="px-2 py-3 text-center text-slate-300">{{ row.lost }}</td>
            <td class="px-2 py-3 text-center text-slate-400">{{ row.goals_for }}</td>
            <td class="px-2 py-3 text-center text-slate-400">{{ row.goals_against }}</td>
            <td
              class="px-2 py-3 text-center font-medium"
              :class="
                row.goal_difference > 0
                  ? 'text-emerald-400'
                  : row.goal_difference < 0
                    ? 'text-red-400'
                    : 'text-slate-400'
              "
            >
              {{ formatGoalDifference(row.goal_difference) }}
            </td>
            <td class="px-4 py-3 text-center text-base font-semibold text-white">
              {{ row.points }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
