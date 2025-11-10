<template>
  <button
    class="btn btn-primary w-100 py-2 fw-semibold"
    type="button"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>

<script setup lang="ts"></script>
