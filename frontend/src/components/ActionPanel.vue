<script setup lang="ts">
import { computed, ref, watch } from 'vue'

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

const baseButton =
  'inline-flex items-center justify-center rounded-lg px-3.5 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-40'
const secondaryButton = `${baseButton} border border-white/10 bg-white/5 text-slate-200 hover:bg-white/10`
const primaryButton = `${baseButton} bg-emerald-500 text-slate-950 hover:bg-emerald-400`

function playSelectedWeek(): void {
  if (selectedWeek.value !== null) {
    emit('play-week', selectedWeek.value)
  }
}
</script>

<template>
  <div class="rounded-2xl border border-white/5 bg-slate-900/50 p-4">
    <div class="flex flex-wrap items-center gap-3">
      <button
        v-if="!hasFixtures"
        type="button"
        :class="primaryButton"
        :disabled="busy"
        @click="emit('generate')"
      >
        {{ activeAction === 'generate' ? 'Generating…' : 'Generate Fixtures' }}
      </button>

      <template v-else>
        <div class="flex items-center gap-2">
          <select
            v-model.number="selectedWeek"
            :disabled="busy || playableWeeks.length === 0"
            class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 disabled:opacity-40"
          >
            <option v-for="week in playableWeeks" :key="week" :value="week">Week {{ week }}</option>
            <option v-if="playableWeeks.length === 0" :value="null">No weeks left</option>
          </select>
          <button
            type="button"
            :class="secondaryButton"
            :disabled="busy || selectedWeek === null"
            @click="playSelectedWeek"
          >
            {{ activeAction === 'week' ? 'Playing…' : 'Play Week' }}
          </button>
        </div>

        <button
          type="button"
          :class="secondaryButton"
          :disabled="busy || isComplete"
          @click="emit('play-next')"
        >
          {{ activeAction === 'next' ? 'Playing…' : 'Play Next Week' }}
        </button>

        <button
          type="button"
          :class="primaryButton"
          :disabled="busy || isComplete"
          @click="emit('play-all')"
        >
          {{ activeAction === 'all' ? 'Playing…' : 'Play All Remaining' }}
        </button>
      </template>

      <span
        v-if="isComplete"
        class="ml-auto rounded-full bg-amber-400/15 px-3 py-1 text-xs font-medium text-amber-300"
      >
        Season complete
      </span>
    </div>
  </div>
</template>
