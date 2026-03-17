<template>
  <div class="relative bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-6 overflow-hidden transition-transform duration-200 hover:-translate-y-0.5">

    <div
      class="absolute bottom-0 left-0 right-0 h-[3px] rounded-b-2xl"
      :class="{
        'bg-[#FF3D6B]': variant === 'expense',
        'bg-[#00E5A0]': variant === 'income',
        'bg-[#4F8EF7]': variant === 'balance',
      }"
    />

    <div class="flex justify-between items-start mb-5">
      <span class="text-[10px] font-bold tracking-[0.12em] uppercase text-[#4A6080]">{{ label }}</span>
      <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/[0.04] text-lg shrink-0">{{ icon }}</div>
    </div>

    <div class="flex items-baseline gap-1.5 mb-5">
      <span class="font-mono text-sm font-medium text-[#4A6080]">R$</span>
      <span
        class="font-mono text-[26px] font-bold tracking-tight leading-none"
        :class="{
          'text-[#FF3D6B]': variant === 'expense',
          'text-[#00E5A0]': variant === 'income',
          'text-[#4F8EF7]': variant === 'balance',
        }"
      >{{ formattedValue }}</span>
    </div>

    <div class="flex items-center gap-2">
      <span
        class="font-mono text-[11px] font-bold px-2 py-0.5 rounded-full"
        :class="trend >= 0 ? 'text-[#00E5A0] bg-[#00E5A0]/10' : 'text-[#FF3D6B] bg-[#FF3D6B]/10'"
      >{{ trend >= 0 ? '▲' : '▼' }} {{ Math.abs(trend) }}%</span>
      <span class="text-[11px] text-[#4A6080]">vs. mês anterior</span>
    </div>

  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  label:   string
  value:   number
  icon:    string
  trend:   number
  variant: 'expense' | 'income' | 'balance'
}>()

const formattedValue = computed(() =>
  new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Math.abs(props.value))
)
</script>
