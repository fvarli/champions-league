<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import SpinnerIcon from '@/components/SpinnerIcon.vue'

const props = withDefaults(
  defineProps<{
    open: boolean
    title: string
    message?: string
    confirmLabel?: string
    busy?: boolean
    variant?: 'primary' | 'danger'
  }>(),
  { confirmLabel: 'Confirm', busy: false, variant: 'primary' },
)

const emit = defineEmits<{ confirm: []; cancel: [] }>()

const confirmClass = computed(() =>
  props.variant === 'danger'
    ? 'bg-rose-500 text-white hover:bg-rose-400'
    : 'bg-emerald-500 text-slate-950 hover:bg-emerald-400',
)

const panel = ref<HTMLElement | null>(null)

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape' && props.open && !props.busy) {
    emit('cancel')
  }
}

watch(
  () => props.open,
  async (open) => {
    if (open) {
      await nextTick()
      panel.value?.querySelector('button')?.focus()
    }
  },
)

onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Transition name="fade">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
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
          :aria-label="title"
        >
          <h2 class="text-base font-semibold text-white">{{ title }}</h2>
          <p v-if="message" class="mt-2 text-sm text-slate-400">{{ message }}</p>

          <div class="mt-6 flex justify-end gap-3">
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="busy"
              @click="emit('cancel')"
            >
              Cancel
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
              :class="confirmClass"
              :disabled="busy"
              @click="emit('confirm')"
            >
              <SpinnerIcon v-if="busy" />
              {{ busy ? 'Working…' : confirmLabel }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
