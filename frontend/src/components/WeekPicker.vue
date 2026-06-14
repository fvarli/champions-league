<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'

const props = withDefaults(
  defineProps<{
    modelValue: number | null
    options: number[]
    disabled?: boolean
  }>(),
  { disabled: false },
)

const emit = defineEmits<{ 'update:modelValue': [week: number] }>()

const root = ref<HTMLElement | null>(null)
const open = ref(false)

function toggle(): void {
  if (props.disabled) {
    return
  }

  open.value = !open.value
}

function close(): void {
  open.value = false
}

function select(week: number): void {
  emit('update:modelValue', week)
  close()
}

function onDocumentMousedown(event: MouseEvent): void {
  if (open.value && root.value && !root.value.contains(event.target as Node)) {
    close()
  }
}

onMounted(() => document.addEventListener('mousedown', onDocumentMousedown))
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocumentMousedown))
</script>

<template>
  <div ref="root" class="relative w-full sm:w-auto" @keydown.esc="close">
    <button
      type="button"
      :disabled="disabled"
      aria-haspopup="listbox"
      :aria-expanded="open"
      aria-label="Select week to play"
      class="inline-flex w-full items-center justify-between gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 transition duration-200 hover:bg-white/10 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40 sm:w-36"
      @click="toggle"
    >
      <span>{{ modelValue !== null ? `Week ${modelValue}` : 'No weeks left' }}</span>
      <svg
        class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200"
        :class="open ? 'rotate-180' : ''"
        viewBox="0 0 20 20"
        fill="currentColor"
        aria-hidden="true"
      >
        <path
          fill-rule="evenodd"
          d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z"
          clip-rule="evenodd"
        />
      </svg>
    </button>

    <Transition name="pop">
      <ul
        v-if="open && options.length > 0"
        role="listbox"
        class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-white/10 bg-slate-900 p-1 shadow-2xl shadow-black/50 sm:w-36"
      >
        <li
          v-for="week in options"
          :key="week"
          role="option"
          :aria-selected="week === modelValue"
          class="flex cursor-pointer items-center justify-between rounded-md px-3 py-1.5 text-sm transition-colors"
          :class="
            week === modelValue
              ? 'bg-emerald-500/15 text-emerald-300'
              : 'text-slate-200 hover:bg-white/5'
          "
          @click="select(week)"
        >
          <span>Week {{ week }}</span>
          <svg
            v-if="week === modelValue"
            class="h-4 w-4"
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
        </li>
      </ul>
    </Transition>
  </div>
</template>
