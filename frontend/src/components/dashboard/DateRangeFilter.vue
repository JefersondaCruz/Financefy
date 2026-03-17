<template>
  <div class="flex items-center gap-3 bg-[#0D1526] border border-[#1E2D45] rounded-xl px-4 py-2.5">
    <span class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] whitespace-nowrap">Período</span>

    <div class="flex items-center gap-1.5">
      <button
        class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/5 text-white text-lg leading-none hover:bg-white/10 transition-colors"
        @click="shift(-1)"
      >‹</button>

      <select
        v-model="selectedMonth"
        class="bg-white/[0.06] border-none text-white text-[13px] font-semibold px-2 py-1 rounded-lg outline-none cursor-pointer appearance-none"
        @change="emitChange"
      >
        <option v-for="(m, i) in months" :key="i" :value="i + 1" class="bg-[#0D1526]">{{ m }}</option>
      </select>

      <select
        v-model="selectedYear"
        class="bg-white/[0.06] border-none text-white text-[13px] font-semibold px-2 py-1 rounded-lg outline-none cursor-pointer appearance-none"
        @change="emitChange"
      >
        <option v-for="y in years" :key="y" :value="y" class="bg-[#0D1526]">{{ y }}</option>
      </select>

      <button
        class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/5 text-white text-lg leading-none hover:bg-white/10 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
        :disabled="isCurrentMonth"
        @click="shift(1)"
      >›</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { DateFilter } from '@/types/finance'

const emit = defineEmits<{ (e: 'change', value: DateFilter): void }>()

const now = new Date()
const selectedMonth = ref(now.getMonth() + 1)
const selectedYear  = ref(now.getFullYear())

const months = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']
const years  = computed(() => {
  const y = now.getFullYear()
  return Array.from({ length: 5 }, (_, i) => y - 4 + i)
})

const isCurrentMonth = computed(
  () => selectedMonth.value === now.getMonth() + 1 && selectedYear.value === now.getFullYear()
)
const emitChange = () => {
  const y = selectedYear.value
  const m = selectedMonth.value
  const start_date = `${y}-${String(m).padStart(2, '0')}-01`
  const lastDay    = new Date(y, m, 0).getDate()
  const end_date   = `${y}-${String(m).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`
  emit('change', { start_date, end_date })
}

const shift = (delta: number) => {
  let m = selectedMonth.value + delta
  let y = selectedYear.value
  if (m > 12) { m = 1; y++ }
  if (m < 1)  { m = 12; y-- }
  selectedMonth.value = m
  selectedYear.value  = y
  emitChange()
}
</script>
