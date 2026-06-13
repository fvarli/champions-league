<script setup lang="ts">
withDefaults(defineProps<{ label?: string }>(), { label: 'Loading…' })
</script>

<template>
  <div class="flex flex-col items-center justify-center py-20 text-slate-400">
    <span
      class="h-9 w-9 animate-spin rounded-full border-2 border-white/10 border-t-emerald-400"
      aria-hidden="true"
    />
    <p class="mt-4 text-sm">{{ label }}</p>
  </div>
</template>
