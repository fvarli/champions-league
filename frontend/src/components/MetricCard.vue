<script setup lang="ts">
import { computed } from 'vue'

type Accent = 'emerald' | 'sky' | 'amber' | 'slate'

const props = withDefaults(
  defineProps<{
    label: string
    value: string | number
    hint?: string
    accent?: Accent
  }>(),
  { accent: 'emerald' },
)

const accentBar = computed<Record<Accent, string>>(() => ({
  emerald: 'bg-emerald-400',
  sky: 'bg-sky-400',
  amber: 'bg-amber-400',
  slate: 'bg-slate-500',
}))
</script>

<template>
  <div
    class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-900/50 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-white/10 hover:bg-slate-900/70"
  >
    <span
      class="absolute inset-y-0 left-0 w-1"
      :class="accentBar[props.accent]"
      aria-hidden="true"
    />
    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ label }}</p>
    <p class="mt-2 text-2xl font-semibold text-white">{{ value }}</p>
    <p v-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
  </div>
</template>
