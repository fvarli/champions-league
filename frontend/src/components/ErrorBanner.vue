<script setup lang="ts">
defineProps<{ message: string | null }>()
defineEmits<{ dismiss: [] }>()
</script>

<template>
  <div
    v-if="message"
    class="flex items-start justify-between gap-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200"
    role="alert"
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
          d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9zm1-4a1 1 0 100 2 1 1 0 000-2z"
          clip-rule="evenodd"
        />
      </svg>
      <span>{{ message }}</span>
    </div>
    <button
      type="button"
      class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium text-red-200/80 transition hover:bg-red-500/20 hover:text-red-100"
      @click="$emit('dismiss')"
    >
      Dismiss
    </button>
  </div>
</template>
