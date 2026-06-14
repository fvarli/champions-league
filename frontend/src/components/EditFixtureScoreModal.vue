<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import SpinnerIcon from '@/components/SpinnerIcon.vue'
import type { Fixture } from '@/types/league'

const props = withDefaults(
  defineProps<{
    open: boolean
    fixture: Fixture | null
    busy?: boolean
  }>(),
  { busy: false },
)

const emit = defineEmits<{ save: [home: number, away: number]; cancel: [] }>()

const home = ref(0)
const away = ref(0)
const panel = ref<HTMLElement | null>(null)

function clamp(value: number): number {
  return Math.max(0, Math.min(20, Math.round(Number.isFinite(value) ? value : 0)))
}

watch(
  () => props.open,
  async (open) => {
    if (open && props.fixture) {
      home.value = props.fixture.home_score ?? 0
      away.value = props.fixture.away_score ?? 0
      await nextTick()
      panel.value?.querySelector('input')?.focus()
    }
  },
)

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape' && props.open && !props.busy) {
    emit('cancel')
  }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))

function save(): void {
  emit('save', clamp(home.value), clamp(away.value))
}

const inputClass =
  'h-14 w-16 rounded-lg border border-white/10 bg-white/5 text-center text-xl font-semibold text-white transition focus-visible:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none'
</script>

<template>
  <Transition name="fade">
    <div v-if="open && fixture" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div
        class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
        aria-hidden="true"
        @click="!busy && emit('cancel')"
      />

      <Transition name="pop" appear>
        <div
          ref="panel"
          class="relative w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 p-6 shadow-2xl shadow-black/50"
          role="dialog"
          aria-modal="true"
          aria-label="Edit match result"
        >
          <h2 class="text-base font-semibold text-white">Edit result</h2>
          <p class="mt-1 text-sm text-slate-400">Week {{ fixture.week }} · scores from 0 to 20.</p>

          <form class="mt-6 flex items-center justify-center gap-4" @submit.prevent="save">
            <div class="flex-1 text-right">
              <span class="text-sm font-medium text-slate-200">{{ fixture.home_team.name }}</span>
            </div>

            <label class="sr-only" :for="'home-score'">{{ fixture.home_team.name }} score</label>
            <input
              id="home-score"
              v-model.number="home"
              type="number"
              min="0"
              max="20"
              step="1"
              inputmode="numeric"
              :class="inputClass"
            />

            <span class="text-slate-500">:</span>

            <label class="sr-only" :for="'away-score'">{{ fixture.away_team.name }} score</label>
            <input
              id="away-score"
              v-model.number="away"
              type="number"
              min="0"
              max="20"
              step="1"
              inputmode="numeric"
              :class="inputClass"
            />

            <div class="flex-1 text-left">
              <span class="text-sm font-medium text-slate-200">{{ fixture.away_team.name }}</span>
            </div>
          </form>

          <div class="mt-7 flex justify-end gap-3">
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="busy"
              @click="emit('cancel')"
            >
              Cancel
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="busy"
              @click="save"
            >
              <SpinnerIcon v-if="busy" />
              {{ busy ? 'Saving…' : 'Save result' }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
