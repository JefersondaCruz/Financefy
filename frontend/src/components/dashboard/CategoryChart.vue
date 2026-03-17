<template>
  <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-6">
    <div class="flex items-center justify-between mb-5">
      <h4 class="text-sm font-bold text-white tracking-wide">Despesas por Categoria</h4>
      <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-[#4F8EF7]/12 text-[#4F8EF7]">
        {{ categoryData.length }} categorias
      </span>
    </div>

    <div class="flex gap-6 items-center">
      <div class="relative w-[160px] h-[160px] shrink-0">
        <canvas ref="canvasRef" />
        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span class="font-mono text-[12px] font-bold text-white leading-tight">R$ {{ totalFormatted }}</span>
          <span class="text-[9px] text-[#4A6080] uppercase tracking-widest mt-0.5">total</span>
        </div>
      </div>

      <ul class="flex-1 flex flex-col gap-2.5 max-h-[180px] overflow-y-auto">
        <li v-for="(item, i) in categoryData" :key="i" class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: item.color }" />
          <span class="flex-1 text-[12px] text-[#4A6080] truncate">{{ item.name }}</span>
          <span class="font-mono text-[11px] text-[#4A6080] min-w-[34px] text-right">{{ item.pct }}%</span>
          <span class="font-mono text-[12px] font-semibold text-white min-w-[72px] text-right">R$ {{ item.formatted }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import Chart from 'chart.js/auto'
import type { Transaction } from '@/types/finance'

const props = defineProps<{ transactions: Transaction[] }>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
let chart: Chart | null = null

const COLORS = ['#4F8EF7','#00E5A0','#F59E0B','#FF3D6B','#A78BFA','#06B6D4','#FB923C','#34D399']

const categoryData = computed(() => {
  const map: Record<string, number> = {}
  props.transactions
    .filter(t => t.category.type === 'expense')
    .forEach(t => { map[t.category.name] = (map[t.category.name] || 0) + Number(t.amount) })

  const total = Object.values(map).reduce((s, v) => s + v, 0)
  return Object.entries(map)
    .sort((a, b) => b[1] - a[1])
    .map(([name, value], i) => ({
      name, value,
      color: COLORS[i % COLORS.length],
      pct: total ? ((value / total) * 100).toFixed(1) : '0',
      formatted: new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2 }).format(value),
    }))
})

const totalFormatted = computed(() =>
  new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2 }).format(
    categoryData.value.reduce((s, c) => s + c.value, 0)
  )
)

const renderChart = () => {
  if (!canvasRef.value) return
  chart?.destroy()
  const data = categoryData.value
  if (!data.length) return
  chart = new Chart(canvasRef.value, {
    type: 'doughnut',
    data: {
      labels: data.map(d => d.name),
      datasets: [{
        data: data.map(d => d.value),
        backgroundColor: data.map(d => d.color + 'CC'),
        borderColor: data.map(d => d.color),
        borderWidth: 2,
        hoverOffset: 8,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0D1526',
          borderColor: '#1E2D45',
          borderWidth: 1,
          padding: 10,
          callbacks: { label: (c) => `  R$ ${Number(c.parsed).toFixed(2)}` },
        },
      },
    },
  })
}

watch(() => props.transactions, renderChart, { deep: true })
onMounted(renderChart)
onBeforeUnmount(() => chart?.destroy())
</script>
