<template>
  <div
    class="rounded-xl border px-4 py-3 text-[12px] font-semibold"
    :class="variantClasses"
    role="status"
  >
    <div class="flex items-start justify-between gap-3">
      <p class="leading-relaxed">{{ message }}</p>
      <button
        v-if="actionLabel"
        type="button"
        class="shrink-0 rounded-lg border px-3 py-1 text-[11px] font-bold transition-colors"
        :class="actionClasses"
        @click="$emit('action')"
      >
        {{ actionLabel }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  message: string
  variant?: 'error' | 'success' | 'info'
  actionLabel?: string
}>(), {
  variant: 'info',
  actionLabel: '',
})

defineEmits<{ (e: 'action'): void }>()

const variantClasses = computed(() => ({
  'border-[#FF3D6B]/30 bg-[#FF3D6B]/10 text-[#FF3D6B]': props.variant === 'error',
  'border-[#00E5A0]/30 bg-[#00E5A0]/10 text-[#00E5A0]': props.variant === 'success',
  'border-[#4F8EF7]/30 bg-[#4F8EF7]/10 text-[#4F8EF7]': props.variant === 'info',
}))

const actionClasses = computed(() => ({
  'border-[#FF3D6B]/30 text-[#FF3D6B] hover:bg-[#FF3D6B]/10': props.variant === 'error',
  'border-[#00E5A0]/30 text-[#00E5A0] hover:bg-[#00E5A0]/10': props.variant === 'success',
  'border-[#4F8EF7]/30 text-[#4F8EF7] hover:bg-[#4F8EF7]/10': props.variant === 'info',
}))
</script>
