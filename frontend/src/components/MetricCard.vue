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

const accents: Record<Accent, { bar: string; glow: string; line: string }> = {
  emerald: { bar: 'bg-emerald-400', glow: 'bg-emerald-500/10', line: 'text-emerald-400/35' },
  sky: { bar: 'bg-sky-400', glow: 'bg-sky-500/10', line: 'text-sky-400/35' },
  amber: { bar: 'bg-amber-400', glow: 'bg-amber-500/10', line: 'text-amber-400/35' },
  slate: { bar: 'bg-slate-400', glow: 'bg-slate-500/10', line: 'text-slate-400/30' },
}

const accent = computed(() => accents[props.accent])
</script>

<template>
  <div
    class="relative overflow-hidden rounded-2xl border border-white/5 bg-slate-900/50 p-4 transition duration-200 hover:-translate-y-1 hover:border-white/10 hover:bg-slate-900/70 hover:shadow-lg hover:shadow-black/30"
  >
    <span class="absolute inset-y-0 left-0 w-1" :class="accent.bar" aria-hidden="true" />
    <span
      class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full blur-2xl"
      :class="accent.glow"
      aria-hidden="true"
    />

    <svg
      class="pointer-events-none absolute bottom-2.5 right-3 h-7 w-20"
      :class="accent.line"
      viewBox="0 0 80 28"
      fill="none"
      aria-hidden="true"
    >
      <polyline
        points="0,22 12,16 24,19 36,9 48,13 60,5 72,8 80,3"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>

    <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ label }}</p>
    <p class="mt-2 text-2xl font-semibold tracking-tight text-white">{{ value }}</p>
    <p v-if="hint" class="mt-0.5 text-xs text-slate-500">{{ hint }}</p>
  </div>
</template>
