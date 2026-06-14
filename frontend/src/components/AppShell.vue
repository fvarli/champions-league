<script setup lang="ts">
withDefaults(defineProps<{ subtitle?: string }>(), {
  subtitle: 'League simulation dashboard',
})
</script>

<template>
  <div class="relative min-h-screen bg-slate-950 text-slate-200">
    <div class="hero-grid pointer-events-none fixed inset-0" aria-hidden="true" />
    <div
      class="pointer-events-none fixed inset-x-0 top-0 h-80 bg-gradient-to-b from-emerald-500/10 via-sky-500/5 to-transparent"
      aria-hidden="true"
    />
    <div
      class="pointer-events-none fixed left-1/2 top-0 h-72 w-[36rem] -translate-x-1/2 rounded-full bg-emerald-500/5 blur-3xl"
      aria-hidden="true"
    />

    <header class="relative border-b border-white/5 bg-slate-950/60 backdrop-blur">
      <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-5 sm:px-6">
        <div class="flex items-center gap-3">
          <div
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30"
          >
            <svg
              class="h-6 w-6"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.6"
              aria-hidden="true"
            >
              <circle cx="12" cy="12" r="9" />
              <path stroke-linejoin="round" d="m12 7 3.2 2.3-1.2 3.7h-4l-1.2-3.7L12 7Z" />
            </svg>
          </div>
          <div>
            <h1 class="text-lg font-semibold tracking-tight text-white">Champions League</h1>
            <p class="text-xs text-slate-400">{{ subtitle }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div
            class="hidden items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] font-medium text-slate-400 md:inline-flex"
          >
            <span>Laravel 12</span>
            <span class="text-slate-600" aria-hidden="true">•</span>
            <span>Vue 3</span>
            <span class="text-slate-600" aria-hidden="true">•</span>
            <span class="text-emerald-300/80">Monte Carlo</span>
          </div>
          <slot name="actions" />
        </div>
      </div>
    </header>

    <main class="relative mx-auto max-w-7xl px-4 py-6 sm:px-6">
      <slot />
    </main>
  </div>
</template>
