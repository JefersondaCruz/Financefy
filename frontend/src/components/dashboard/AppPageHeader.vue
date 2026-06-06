<template>
  <header class="flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-4">
      <button
        type="button"
        class="w-10 h-10 flex flex-col items-center justify-center gap-[5px] bg-[#0D1526] border border-[#1E2D45] rounded-xl hover:border-[#4F8EF7] transition-colors"
        aria-label="Abrir menu"
        @click="$emit('open-menu')"
      >
        <span class="block w-4 h-[1.5px] bg-white rounded" />
        <span class="block w-4 h-[1.5px] bg-white rounded" />
        <span class="block w-4 h-[1.5px] bg-white rounded" />
      </button>
      <div>
        <h1 class="text-xl font-extrabold tracking-tight text-white leading-tight">{{ title }}</h1>
        <p v-if="subtitle" class="text-[12px] text-[#4A6080] mt-0.5">{{ subtitle }}</p>
      </div>
    </div>

    <div v-if="$slots.actions" class="flex items-center gap-3 flex-wrap">
      <slot name="actions" />
    </div>
  </header>
</template>

<script setup lang="ts">
defineProps<{
  title: string
  subtitle?: string
}>()

defineEmits<{
  (e: 'open-menu'): void
}>()
</script>
