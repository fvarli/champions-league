<script setup lang="ts">
type EmptyIcon = 'clock' | 'chart' | 'pitch'

withDefaults(defineProps<{ title: string; message?: string; icon?: EmptyIcon }>(), {
  icon: 'clock',
})
</script>

<template>
  <div
    class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-white/10 bg-slate-900/30 px-6 py-12 text-center"
  >
    <div
      class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400 ring-1 ring-emerald-500/20"
    >
      <svg
        class="h-6 w-6"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.6"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <template v-if="icon === 'clock'">
          <circle cx="12" cy="12" r="9" />
          <path d="M12 8v4l2.5 2.5" />
        </template>
        <template v-else-if="icon === 'chart'">
          <path d="M4 19V5m0 14h16M8 16v-4m4 4V9m4 7v-6" />
        </template>
        <template v-else>
          <rect x="3" y="5" width="18" height="14" rx="1.5" />
          <path d="M12 5v14M3 9h3v6H3m18-6h-3v6h3" />
        </template>
      </svg>
    </div>
    <h3 class="mt-4 text-sm font-semibold text-white">{{ title }}</h3>
    <p v-if="message" class="mt-1 max-w-sm text-sm text-slate-400">{{ message }}</p>
    <div v-if="$slots.default" class="mt-5">
      <slot />
    </div>
  </div>
</template>
