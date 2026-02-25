import { ref } from 'vue'

const toasts = ref([])
const defaultDuration = 4000

export function useToast() {
  function add(message, options = {}) {
    const id = Date.now()
    const toast = {
      id,
      message,
      type: options.type ?? 'info',
    }
    toasts.value.push(toast)
    const duration = options.duration ?? defaultDuration
    if (duration > 0) {
      setTimeout(() => remove(id), duration)
    }
    return id
  }

  function remove(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  return { toasts, add, remove }
}
