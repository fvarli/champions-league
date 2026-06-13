<script setup lang="ts">
import { useAppStore } from '@/stores/app'

const app = useAppStore()
</script>

<template>
  <main
    class="flex min-h-screen flex-col items-center justify-center gap-3 bg-slate-50 text-slate-800"
  >
    <h1 class="text-3xl font-semibold">{{ app.name }}</h1>
    <p class="text-slate-500">Football league simulation — frontend ready.</p>
  </main>
</template>
