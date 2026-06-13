import { ref } from 'vue'
import { defineStore } from 'pinia'

/**
 * Root application store. Holds cross-cutting UI state; feature stores are
 * added under `src/stores` as the application grows.
 */
export const useAppStore = defineStore('app', () => {
  const name = ref('Champions League')

  return { name }
})
