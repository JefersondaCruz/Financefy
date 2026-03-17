<template>
  <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-6">
    <div class="flex items-center justify-between mb-5">
      <h4 class="text-sm font-bold text-white tracking-wide">Fluxo de Caixa</h4>
      <div class="flex gap-4">
        <span class="flex items-center gap-2 text-[11px] font-semibold text-[#4A6080]">
          <span class="block w-4 h-0.5 rounded bg-[#00E5A0]" />
          Receitas
        </span>
        <span class="flex items-center gap-2 text-[11px] font-semibold text-[#4A6080]">
          <span class="block w-4 h-0.5 rounded bg-[#FF3D6B]" />
          Despesas
        </span>
      </div>
    </div>
    <div class="h-[240px] relative">
      <canvas ref="canvasRef" />
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

const chartData = computed(() => {
  const map: Record<string, { income: number; expense: number }> = {}
  props.transactions.forEach(t => {
    const d = new Date(t.transaction_date)
    const key = `${d.getDate().toString().padStart(2,'0')}/${(d.getMonth()+1).toString().padStart(2,'0')}`
    if (!map[key]) map[key] = { income: 0, expense: 0 }
    if (t.category.type === 'income') map[key].income  += Number(t.amount)
    else                              map[key].expense += Math.abs(Number(t.amount))
  })
  const sorted = Object.keys(map)
    .sort((a, b) => {
      const [da = 0, ma = 0] = a.split('/').map(Number)
      const [db = 0, mb = 0] = b.split('/').map(Number)

      return ma !== mb ? ma - mb : da - db
    })
    .slice(-14)
  return {
    labels:   sorted,
    incomes:  sorted.map(k => map[k]?.income  || 0),
    expenses: sorted.map(k => map[k]?.expense || 0),
  }
})

const renderChart = () => {
  if (!canvasRef.value) return
  chart?.destroy()
  const { labels, incomes, expenses } = chartData.value
  chart = new Chart(canvasRef.value, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Receitas',
          data: incomes,
          borderColor: '#00E5A0',
          backgroundColor: 'rgba(0,229,160,0.06)',
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: '#00E5A0',
          pointHoverRadius: 6,
          tension: 0.4,
          fill: true,
        },
        {
          label: 'Despesas',
          data: expenses,
          borderColor: '#FF3D6B',
          backgroundColor: 'rgba(255,61,107,0.06)',
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: '#FF3D6B',
          pointHoverRadius: 6,
          tension: 0.4,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0D1526',
          borderColor: '#1E2D45',
          borderWidth: 1,
          padding: 12,
          titleColor: '#8CA4C6',
          bodyColor: '#E8EEFF',
          callbacks: { label: (c) => `  ${c.dataset.label}: R$ ${Number(c.parsed.y).toFixed(2)}` },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#4A6080', font: { size: 11 } },
          border: { color: '#1E2D45' },
        },
        y: {
          grid: { color: 'rgba(30,45,69,0.8)' },
          ticks: { color: '#4A6080', font: { size: 11 }, callback: (v) => `R$${v}` },
          border: { color: 'transparent' },
        },
      },
    },
  })
}

watch(() => props.transactions, renderChart, { deep: true })
onMounted(renderChart)
onBeforeUnmount(() => chart?.destroy())
</script>
