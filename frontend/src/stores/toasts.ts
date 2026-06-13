import { ref } from 'vue'
import { defineStore } from 'pinia'

export type ToastVariant = 'success' | 'error' | 'info'

export interface Toast {
  id: number
  message: string
  variant: ToastVariant
}

export const useToastStore = defineStore('toasts', () => {
  const toasts = ref<Toast[]>([])
  let nextId = 0

  function dismiss(id: number): void {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  function push(message: string, variant: ToastVariant = 'success', timeout = 4000): void {
    const id = nextId++
    toasts.value.push({ id, message, variant })

    if (timeout > 0) {
      window.setTimeout(() => dismiss(id), timeout)
    }
  }

  return { toasts, push, dismiss }
})
