<script setup lang="ts">
import type { Fixture } from '@/types/league'

defineProps<{ fixtures: Fixture[] }>()
</script>

<template>
  <div
    v-if="fixtures.length > 0"
    class="flex items-center gap-3 rounded-xl border border-white/5 bg-slate-900/40 px-4 py-2.5"
  >
    <span
      class="flex shrink-0 items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-emerald-300"
    >
      <span class="relative flex h-2 w-2">
        <span
          class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400/70 motion-reduce:animate-none"
        />
        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400" />
      </span>
      Latest
    </span>

    <div class="flex min-w-0 items-center gap-3 overflow-x-auto text-sm">
      <span
        v-for="(fixture, index) in fixtures"
        :key="fixture.id"
        class="flex shrink-0 items-center gap-2 whitespace-nowrap"
      >
        <span v-if="index > 0" class="text-slate-600" aria-hidden="true">·</span>
        <span class="text-slate-300">{{ fixture.home_team.name }}</span>
        <span class="rounded bg-white/5 px-1.5 py-0.5 font-semibold tabular-nums text-white">
          {{ fixture.home_score }}–{{ fixture.away_score }}
        </span>
        <span class="text-slate-300">{{ fixture.away_team.name }}</span>
      </span>
    </div>
  </div>
</template>
