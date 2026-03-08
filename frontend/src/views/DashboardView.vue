<template>
  <div class="min-h-screen bg-[#0f172a] text-white flex flex-col gap-8 px-6 py-6 md:px-12">

    <!-- ── Hamburger ── -->
    <button
      class="fixed top-5 left-5 z-[1001] bg-[#1e293b] text-white text-2xl px-3 py-1 rounded-lg hover:bg-[#00d084] transition-colors"
      @click="toggleMenu"
    >
      ☰
    </button>

    <!-- ── Side Menu ── -->
    <aside
      class="fixed top-0 h-full w-[230px] bg-[#1e293b] shadow-xl pt-[70px] z-[1000] transition-all duration-300"
      :class="isMenuOpen ? 'left-0' : '-left-[250px]'"
    >
      <ul class="list-none p-0 m-0">
        <li
          v-for="item in menuItems"
          :key="item.label"
          class="px-6 py-4 text-[#ccc] text-lg cursor-pointer hover:bg-[#00d084] hover:text-white transition-colors"
          @click="item.action"
        >
          {{ item.icon }} {{ item.label }}
        </li>
      </ul>
    </aside>

    <!-- ── Overlay ── -->
    <div
      v-if="isMenuOpen"
      class="fixed inset-0 bg-black/40 z-[999]"
      @click="toggleMenu"
    />

    <!-- ── Header ── -->
    <header class="flex items-center justify-between bg-[#1e293b] rounded-xl px-6 py-4 pl-14">
      <div>
        <h2 class="text-2xl font-bold text-white m-0">Dashboard</h2>
        <p class="text-[#94a3b8] text-sm mt-1 m-0">Visão geral de Fevereiro 2026</p>
      </div>
      <button
        class="bg-[#3b82f6] hover:bg-[#2563eb] text-white font-medium px-4 py-2 rounded-lg transition-colors whitespace-nowrap"
        @click="openModal"
      >
        💬 Nova Transação
      </button>
    </header>

    <!-- ── KPI Cards ── -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-[#1e293b] rounded-xl p-5 shadow-md">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-[#94a3b8] mb-1">Despesas Totais</p>
            <h3 class="text-2xl font-bold text-[#f43f5e]">R$ {{ totalExpenses.toFixed(2) }}</h3>
          </div>
          <div class="p-2 bg-[#f43f5e]/10 rounded-lg text-xl">📉</div>
        </div>
        <div class="flex items-center gap-2 mt-4 text-sm">
          <span class="text-[#f43f5e] font-semibold">↑ +12%</span>
          <span class="text-[#64748b]">vs mês anterior</span>
        </div>
      </div>

      <div class="bg-[#1e293b] rounded-xl p-5 shadow-md">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-[#94a3b8] mb-1">Receitas Totais</p>
            <h3 class="text-2xl font-bold text-[#10b981]">R$ {{ totalIncome.toFixed(2) }}</h3>
          </div>
          <div class="p-2 bg-[#10b981]/10 rounded-lg text-xl">📈</div>
        </div>
        <div class="flex items-center gap-2 mt-4 text-sm">
          <span class="text-[#10b981] font-semibold">↑ +8%</span>
          <span class="text-[#64748b]">vs mês anterior</span>
        </div>
      </div>

      <div class="bg-[#1e293b] rounded-xl p-5 shadow-md">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-[#94a3b8] mb-1">Saldo Líquido</p>
            <h3 class="text-2xl font-bold" :class="netBalance >= 0 ? 'text-[#3b82f6]' : 'text-[#f43f5e]'">
              R$ {{ netBalance.toFixed(2) }}
            </h3>
          </div>
          <div class="p-2 bg-[#3b82f6]/10 rounded-lg text-xl">💰</div>
        </div>
        <div class="flex items-center gap-2 mt-4 text-sm">
          <span class="text-[#3b82f6] font-semibold">{{ netBalance >= 0 ? 'Saudável' : 'Atenção' }}</span>
          <span class="text-[#64748b]">situação financeira</span>
        </div>
      </div>
    </section>

    <!-- ── Charts ── -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-[#1e293b] rounded-xl p-6 flex flex-col">
        <h4 class="text-white font-semibold mb-4">Despesas por Categoria</h4>
        <div class="relative h-[280px]">
          <canvas id="categoryChart"></canvas>
        </div>
        <div class="flex flex-wrap gap-3 justify-center mt-4">
          <div v-for="(cat, i) in categoryChartData" :key="i" class="flex items-center gap-1.5 text-xs text-[#94a3b8]">
            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: cat.color }"></div>
            {{ cat.name }}
          </div>
        </div>
      </div>

      <div class="bg-[#1e293b] rounded-xl p-6 flex flex-col">
        <h4 class="text-white font-semibold mb-4">Tendência de Gastos (30 dias)</h4>
        <div class="relative h-[280px]">
          <canvas id="trendChart"></canvas>
        </div>
        <div class="flex justify-center gap-6 mt-4">
          <div class="flex items-center gap-1.5 text-xs text-[#94a3b8]">
            <div class="w-3.5 h-[3px] rounded bg-[#00d084]"></div>
            Receitas
          </div>
          <div class="flex items-center gap-1.5 text-xs text-[#94a3b8]">
            <div class="w-3.5 h-[3px] rounded bg-[#ff4d4d]"></div>
            Despesas
          </div>
        </div>
      </div>
    </section>

    <!-- ── Transactions Table ── -->
    <section class="bg-[#1e293b] rounded-xl overflow-hidden">
      <div class="px-6 py-4 border-b border-[#334155]">
        <h4 class="text-white font-semibold">Transações Recentes</h4>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="bg-[#0f172a]/50 text-[#94a3b8]">
              <th class="px-5 py-3 font-medium text-left border-b border-[#334155]">Descrição</th>
              <th class="px-5 py-3 font-medium text-left border-b border-[#334155]">Categoria</th>
              <th class="px-5 py-3 font-medium text-left border-b border-[#334155]">Data</th>
              <th class="px-5 py-3 font-medium text-right border-b border-[#334155]">Valor</th>
              <th class="px-5 py-3 font-medium text-right border-b border-[#334155]">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="t in transactions"
              :key="t.id"
              class="border-b border-[#334155] hover:bg-[#334155]/20 transition-colors"
            >
              <td class="px-5 py-3 text-white font-medium">{{ t.description }}</td>
              <td class="px-5 py-3">
                <span
                  class="inline-block px-3 py-0.5 rounded-full text-xs font-medium"
                  :class="t.category.type === 'income' ? 'bg-[#10b981]/15 text-[#10b981]' : 'bg-[#f43f5e]/15 text-[#f43f5e]'"
                >
                  {{ t.category.name }}
                </span>
              </td>
              <td class="px-5 py-3 text-[#94a3b8]">{{ formatDate(t.transaction_date) }}</td>
              <td
                class="px-5 py-3 text-right font-mono font-semibold"
                :class="t.category.type === 'income' ? 'text-[#10b981]' : 'text-[#f43f5e]'"
              >
                {{ t.category.type === 'income' ? '+' : '-' }} R$ {{ Math.abs(Number(t.amount)).toFixed(2) }}
              </td>
              <td class="px-5 py-3">
                <div class="flex gap-2 justify-end">
                  <button class="bg-[#334155] hover:bg-[#00a86b] px-2 py-1 rounded-md transition-colors" @click="openEditModal(t)">✏️</button>
                  <button class="bg-[#334155] hover:bg-[#f43f5e] px-2 py-1 rounded-md transition-colors" @click="openDeleteModal(t)">🗑️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-center items-center gap-4 py-4 text-sm text-[#94a3b8]">
        <button
          class="bg-[#3b82f6] hover:bg-[#2563eb] disabled:opacity-40 disabled:cursor-not-allowed text-white px-4 py-1.5 rounded-lg transition-colors"
          :disabled="currentPage === 1"
          @click="prevPage"
        >Anterior</button>
        <span>Página {{ currentPage }} de {{ lastPage }}</span>
        <button
          class="bg-[#3b82f6] hover:bg-[#2563eb] disabled:opacity-40 disabled:cursor-not-allowed text-white px-4 py-1.5 rounded-lg transition-colors"
          :disabled="currentPage === lastPage"
          @click="nextPage"
        >Próxima</button>
      </div>
    </section>

    <!-- ── Modal: Nova / Editar ── -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-black/70 flex items-center justify-center z-[2000]" @click="closeModal">
      <div class="bg-[#1e293b] p-8 w-[90%] max-w-md rounded-2xl shadow-2xl text-white max-h-[90vh] overflow-y-auto" @click.stop>
        <h2 class="text-center text-[#3b82f6] text-xl font-bold mb-6">
          {{ isEditing ? 'Editar Transação' : 'Nova Transação' }}
        </h2>
        <form @submit.prevent="submitTransaction" class="flex flex-col">

          <label class="text-xs text-[#94a3b8] mb-1">Categoria</label>
          <select v-model="form.category_id" required
            class="w-full px-3 py-2 mb-4 bg-[#0f172a] border border-[#334155] rounded-lg text-white">
            <option value="0" disabled>Selecione...</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>

          <label class="text-xs text-[#94a3b8] mb-1">Descrição</label>
          <input type="text" v-model="form.description" required
            class="w-full px-3 py-2 mb-4 bg-[#0f172a] border border-[#334155] rounded-lg text-white" />

          <label class="text-xs text-[#94a3b8] mb-1">Valor</label>
          <input type="number" step="0.01" v-model="form.amount" required
            class="w-full px-3 py-2 mb-4 bg-[#0f172a] border border-[#334155] rounded-lg text-white" />

          <label class="text-xs text-[#94a3b8] mb-1">Data</label>
          <input type="date" v-model="form.transaction_date" required
            class="w-full px-3 py-2 mb-4 bg-[#0f172a] border border-[#334155] rounded-lg text-white" />

          <label class="text-xs text-[#94a3b8] mb-1">Método de Pagamento</label>
          <select v-model="form.payment_method" required
            class="w-full px-3 py-2 mb-4 bg-[#0f172a] border border-[#334155] rounded-lg text-white">
            <option value="credit_card">Cartão de Crédito</option>
            <option value="pix">Pix</option>
            <option value="money">Dinheiro</option>
            <option value="others">Outros</option>
          </select>

          <label class="flex items-center gap-2 text-sm text-[#94a3b8] mb-4 cursor-pointer">
            <input type="checkbox" v-model="form.is_recurring" class="w-auto" />
            Recorrente?
          </label>

          <div v-if="form.is_recurring">
            <label class="text-xs text-[#94a3b8] mb-1 block">Tipo de Recorrência</label>
            <input type="text" v-model="form.recurrence_type" placeholder="ex: mensal, semanal..."
              class="w-full px-3 py-2 mb-4 bg-[#0f172a] border border-[#334155] rounded-lg text-white" />
          </div>

          <div class="flex gap-3 mt-2">
            <button type="submit" class="flex-1 py-2.5 bg-[#3b82f6] hover:bg-[#2563eb] rounded-lg font-medium transition-colors">Salvar</button>
            <button type="button" class="flex-1 py-2.5 bg-[#f43f5e] hover:bg-[#e02040] rounded-lg font-medium transition-colors" @click="closeModal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── Modal: Deletar ── -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 bg-black/70 flex items-center justify-center z-[2000]" @click="closeDeleteModal">
      <div class="bg-[#1e293b] p-8 w-[90%] max-w-sm rounded-2xl shadow-2xl text-white" @click.stop>
        <h3 class="text-center text-[#f43f5e] font-bold text-lg mb-2">Tem certeza que deseja excluir?</h3>
        <p class="text-center text-[#94a3b8] mb-6">Essa ação não pode ser desfeita.</p>
        <div class="flex gap-3">
          <button class="flex-1 py-2.5 bg-[#334155] hover:bg-[#475569] rounded-lg font-medium transition-colors" @click="closeDeleteModal">Cancelar</button>
          <button class="flex-1 py-2.5 bg-[#f43f5e] hover:bg-[#e02040] rounded-lg font-medium transition-colors" @click="confirmDelete">Excluir</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import Chart from 'chart.js/auto'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'
import api from '@/services/api'

// ── Types ──────────────────────────────────────────────────────────────────
interface Transaction {
  id: number
  description: string
  amount: number
  transaction_date: string
  payment_method: string
  category_id: number
  category: { id: number; name: string; type: 'income' | 'expense' }
  is_recurring: boolean
  recurrence_type: string | null
}

interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  per_page: number
  total: number
  last_page: number
}

interface Category {
  id: number
  name: string
}

// ── State ──────────────────────────────────────────────────────────────────
const isMenuOpen        = ref(false)
const isModalOpen       = ref(false)
const isEditing         = ref(false)
const editingId         = ref<number | null>(null)
const isDeleteModalOpen = ref(false)
const deleteId          = ref<number | null>(null)

const auth         = useAuthStore()
const transactions = ref<Transaction[]>([])
const categories   = ref<Category[]>([])
const currentPage  = ref(1)
const lastPage     = ref(1)
const total        = ref(0)
const perPage      = ref(10)

const categoryChartRef = ref<Chart | null>(null)
const trendChartRef    = ref<Chart | null>(null)

const form = ref({
  category_id: 0,
  description: '',
  amount: 0,
  transaction_date: '',
  payment_method: 'pix',
  is_recurring: false,
  recurrence_type: null as string | null,
})

// ── Static data ────────────────────────────────────────────────────────────

const menuItems = [
  { icon: '📊', label: 'Dashboard', action: () => goTo() },
  { icon: '👤', label: 'Perfil',    action: () => goTo() },
  { icon: '👋', label: 'Sair',      action: () => logout() },
]

// ── Computed ───────────────────────────────────────────────────────────────
const totalExpenses = computed(() =>
  transactions.value.filter((t) => t.category.type === 'expense').reduce((s, t) => s + Number(t.amount), 0)
)
const totalIncome = computed(() =>
  transactions.value.filter((t) => t.category.type === 'income').reduce((s, t) => s + Number(t.amount), 0)
)
const netBalance = computed(() => totalIncome.value - totalExpenses.value)

const COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#06b6d4', '#ef4444', '#84cc16']

const categoryChartData = computed(() => {
  const map: Record<string, { name: string; value: number; color: string }> = {}
  transactions.value
    .filter((t) => t.category.type === 'expense')
    .forEach((t) => {
      const name = t.category.name
      if (!map[name]) map[name] = { name, value: 0, color: COLORS[Object.keys(map).length % COLORS.length] }
      map[name].value += Number(t.amount)
    })
  return Object.values(map)
})

const trendChartData = computed(() => {
  const monthly: Record<string, { income: number; expense: number }> = {}
  transactions.value.forEach((t) => {
    const key = new Date(t.transaction_date).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' })
    if (!monthly[key]) monthly[key] = { income: 0, expense: 0 }
    if (t.category.type === 'income') monthly[key].income += Number(t.amount)
    else monthly[key].expense += Math.abs(Number(t.amount))
  })
  const sorted = Object.keys(monthly)
    .sort((a, b) => new Date(a).getTime() - new Date(b).getTime())
    .slice(-6)
  return {
    labels:   sorted,
    incomes:  sorted.map((m) => monthly[m]?.income  || 0),
    expenses: sorted.map((m) => monthly[m]?.expense || 0),
  }
})

// ── Charts ─────────────────────────────────────────────────────────────────
const renderCategoryChart = () => {
  const ctx = document.getElementById('categoryChart') as HTMLCanvasElement
  if (!ctx) return
  categoryChartRef.value?.destroy()
  const data = categoryChartData.value
  categoryChartRef.value = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: data.map((d) => d.name),
      datasets: [{ data: data.map((d) => d.value), backgroundColor: data.map((d) => d.color), borderWidth: 0, hoverOffset: 6 }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (c) => ` ${c.label}: R$ ${Number(c.parsed).toFixed(2)}` } },
      },
    },
  })
}

const renderTrendChart = () => {
  const ctx = document.getElementById('trendChart') as HTMLCanvasElement
  if (!ctx) return
  trendChartRef.value?.destroy()
  const { labels, incomes, expenses } = trendChartData.value
  trendChartRef.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Receitas',  data: incomes,   borderColor: '#00d084', backgroundColor: 'rgba(0,208,132,.1)',  borderWidth: 2, pointRadius: 0, pointHoverRadius: 6, tension: 0.4, fill: true },
        { label: 'Despesas', data: expenses, borderColor: '#ff4d4d', backgroundColor: 'rgba(255,77,77,.1)',  borderWidth: 2, pointRadius: 0, pointHoverRadius: 6, tension: 0.4, fill: true },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: R$ ${Number(c.parsed.y).toFixed(2)}` } },
      },
      scales: {
        x: { ticks: { color: '#aaa' }, grid: { display: false } },
        y: { ticks: { color: '#aaa', callback: (v) => `R$${v}` }, grid: { color: '#334155' } },
      },
    },
  })
}

const refreshCharts = () => { renderCategoryChart(); renderTrendChart() }

// ── API ────────────────────────────────────────────────────────────────────
const fetchTransactions = async (page = 1) => {
  try {
    const { data } = await api.get<PaginatedResponse<Transaction>>('/transactions', { params: { page, per_page: perPage.value } })
    transactions.value = data.data
    currentPage.value  = data.current_page
    lastPage.value     = data.last_page
    total.value        = data.total
  } catch (e) { console.error(e) }
}

const fetchCategories = async () => {
  try { const { data } = await api.get('/categories'); categories.value = data }
  catch (e) { console.error(e) }
}

const createTransaction = async () => {
  try { await api.post('/transactions', form.value); closeModal(); await fetchTransactions(currentPage.value); refreshCharts() }
  catch (e) { console.error(e) }
}

const updateTransaction = async () => {
  try { await api.put(`/transactions/${editingId.value}`, form.value); closeModal(); await fetchTransactions(currentPage.value); refreshCharts() }
  catch (e) { console.error(e) }
}

const confirmDelete = async () => {
  try { await api.delete(`/transactions/${deleteId.value}`); closeDeleteModal(); await fetchTransactions(currentPage.value); refreshCharts() }
  catch (e) { console.error(e) }
}

// ── Helpers ────────────────────────────────────────────────────────────────
const submitTransaction = () => (isEditing.value ? updateTransaction() : createTransaction())
const openModal         = () => (isModalOpen.value = true)
const closeModal        = () => { isModalOpen.value = false; isEditing.value = false; editingId.value = null; resetForm() }

const openEditModal = (t: Transaction) => {
  isEditing.value   = true
  editingId.value   = t.id
  form.value = {
    category_id: t.category_id,
    description: t.description,
    amount: t.amount,
    transaction_date: t.transaction_date,
    payment_method: t.payment_method,
    is_recurring: t.is_recurring ?? false,
    recurrence_type: t.recurrence_type ?? null,
  }
  isModalOpen.value = true
}

const openDeleteModal  = (t: Transaction) => { deleteId.value = t.id; isDeleteModalOpen.value = true }
const closeDeleteModal = () => { isDeleteModalOpen.value = false; deleteId.value = null }

const resetForm = () => {
  form.value = { category_id: 0, description: '', amount: 0, transaction_date: '', payment_method: 'pix', is_recurring: false, recurrence_type: null }
}

const toggleMenu = () => (isMenuOpen.value = !isMenuOpen.value)
const logout     = () => auth.logout()
const goTo       = () => router.push('/under-construction')

const formatDate = (d: string) => new Date(d).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
const nextPage   = () => { if (currentPage.value < lastPage.value) fetchTransactions(currentPage.value + 1) }
const prevPage   = () => { if (currentPage.value > 1) fetchTransactions(currentPage.value - 1) }

onMounted(async () => { await fetchTransactions(); await fetchCategories(); refreshCharts() })
</script>
