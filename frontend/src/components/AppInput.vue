<template>
  <div class="mb-3">
    <label v-if="label" class="form-label">{{ label }}</label>
    <input
      :type="type"
      class="form-control"
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label?: string
  type?: string
  modelValue: string
}>()

defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()
</script>
