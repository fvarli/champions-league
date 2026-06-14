<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import SpinnerIcon from '@/components/SpinnerIcon.vue'
import WeekPicker from '@/components/WeekPicker.vue'

const props = defineProps<{
  hasFixtures: boolean
  playableWeeks: number[]
  isComplete: boolean
  activeAction: string | null
}>()

const emit = defineEmits<{
  generate: []
  'play-week': [week: number]
  'play-next': []
  'play-all': []
  reset: []
}>()

const selectedWeek = ref<number | null>(props.playableWeeks[0] ?? null)

watch(
  () => props.playableWeeks,
  (weeks) => {
    if (selectedWeek.value === null || !weeks.includes(selectedWeek.value)) {
      selectedWeek.value = weeks[0] ?? null
    }
  },
)

const busy = computed(() => props.activeAction !== null)
const noWeeksLeft = computed(() => props.playableWeeks.length === 0)

const baseButton =
  'inline-flex items-center justify-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium transition duration-200 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40'
const primaryButton = `${baseButton} bg-emerald-500 text-slate-950 hover:bg-emerald-400`
const secondaryButton = `${baseButton} border border-white/10 bg-white/5 text-slate-200 hover:bg-white/10`
const resetButton = `${baseButton} border border-white/10 bg-transparent text-slate-400 hover:border-rose-500/30 hover:bg-rose-500/10 hover:text-rose-300`

const fullWidth = 'w-full sm:w-auto'

function playSelectedWeek(): void {
  if (selectedWeek.value !== null) {
    emit('play-week', selectedWeek.value)
  }
}
</script>

<template>
  <div class="rounded-2xl border border-white/5 bg-slate-900/50 p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
      <button
        v-if="!hasFixtures"
        type="button"
        :class="[primaryButton, fullWidth]"
        :disabled="busy"
        @click="emit('generate')"
      >
        <SpinnerIcon v-if="activeAction === 'generate'" />
        {{ activeAction === 'generate' ? 'Generating…' : 'Generate Fixtures' }}
      </button>

      <template v-else>
        <button
          type="button"
          :class="[primaryButton, fullWidth]"
          :disabled="busy || isComplete"
          @click="emit('play-next')"
        >
          <SpinnerIcon v-if="activeAction === 'next'" />
          {{ activeAction === 'next' ? 'Playing…' : 'Play Next Week' }}
        </button>

        <div class="flex w-full items-center gap-2 sm:w-auto">
          <div class="flex-1 sm:flex-none">
            <WeekPicker
              v-model="selectedWeek"
              :options="playableWeeks"
              :disabled="busy || noWeeksLeft"
            />
          </div>
          <button
            type="button"
            :class="[secondaryButton, 'shrink-0']"
            :disabled="busy || selectedWeek === null"
            @click="playSelectedWeek"
          >
            <SpinnerIcon v-if="activeAction === 'week'" />
            {{ activeAction === 'week' ? 'Playing…' : 'Play Week' }}
          </button>
        </div>

        <button
          type="button"
          :class="[secondaryButton, fullWidth]"
          :disabled="busy || isComplete"
          @click="emit('play-all')"
        >
          <SpinnerIcon v-if="activeAction === 'all'" />
          {{ activeAction === 'all' ? 'Playing…' : 'Play All Remaining' }}
        </button>
      </template>

      <div class="flex w-full items-center gap-3 sm:ml-auto sm:w-auto">
        <span
          v-if="isComplete"
          class="rounded-full bg-amber-400/15 px-3 py-1 text-xs font-medium text-amber-300"
        >
          Season complete
        </span>
        <button
          type="button"
          :class="[resetButton, fullWidth]"
          :disabled="busy"
          @click="emit('reset')"
        >
          <SpinnerIcon v-if="activeAction === 'reset'" />
          {{ activeAction === 'reset' ? 'Resetting…' : 'Reset Season' }}
        </button>
      </div>
    </div>
  </div>
</template>
