<script setup lang="ts">
import { computed } from 'vue'

import { useToastStore, type ToastVariant } from '@/stores/toasts'

const store = useToastStore()

const variantClass = computed<Record<ToastVariant, string>>(() => ({
  success: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-100',
  error: 'border-red-500/30 bg-red-500/10 text-red-100',
  info: 'border-sky-500/30 bg-sky-500/10 text-sky-100',
}))
</script>

<template>
  <div
    class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4"
    aria-live="polite"
    aria-atomic="true"
  >
    <TransitionGroup name="toast">
      <div
        v-for="toast in store.toasts"
        :key="toast.id"
        class="pointer-events-auto flex w-full max-w-sm items-start justify-between gap-3 rounded-xl border px-4 py-3 text-sm shadow-lg shadow-black/30 backdrop-blur"
        :class="variantClass[toast.variant]"
        role="status"
      >
        <div class="flex items-start gap-2">
          <svg
            class="mt-0.5 h-4 w-4 shrink-0"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
          >
            <path
              fill-rule="evenodd"
              d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.1 3.1 6.8-6.8a1 1 0 011.4 0z"
              clip-rule="evenodd"
            />
          </svg>
          <span>{{ toast.message }}</span>
        </div>
        <button
          type="button"
          class="shrink-0 rounded-md px-1.5 text-xs font-medium opacity-70 transition hover:opacity-100"
          aria-label="Dismiss notification"
          @click="store.dismiss(toast.id)"
        >
          ✕
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>
