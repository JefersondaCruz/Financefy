<template>
  <div class="min-h-screen bg-[#07090F] text-white flex">

    <AppSidebar :isOpen="isMenuOpen" activeItem="Transações" @close="isMenuOpen = false" />

    <div class="flex-1 flex flex-col gap-6 px-6 py-6 md:px-8 min-w-0">

      <!-- Topbar -->
      <header class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
          <button
            class="w-10 h-10 flex flex-col items-center justify-center gap-[5px] bg-[#0D1526] border border-[#1E2D45] rounded-xl hover:border-[#4F8EF7] transition-colors"
            @click="isMenuOpen = true"
          >
            <span class="block w-4 h-[1.5px] bg-white rounded" />
            <span class="block w-4 h-[1.5px] bg-white rounded" />
            <span class="block w-4 h-[1.5px] bg-white rounded" />
          </button>
          <div>
            <h1 class="text-xl font-extrabold tracking-tight text-white leading-tight">Transações</h1>
            <p class="text-[12px] text-[#4A6080] mt-0.5">Histórico completo de movimentações</p>
          </div>
        </div>
        <button
          class="flex items-center gap-2 bg-[#4F8EF7] hover:bg-[#3a7de0] text-white text-[13px] font-bold px-4 py-2.5 rounded-xl transition-all hover:-translate-y-px whitespace-nowrap"
          @click="openModal()"
        >
          <span class="text-lg font-light leading-none">+</span>
          Nova Transação
        </button>
      </header>

      <!-- KPI Stats -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="relative bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-4 overflow-hidden">
          <div class="absolute bottom-0 left-0 right-0 h-[3px] rounded-b-2xl bg-[#4F8EF7]" />
          <p class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080] mb-2">Total</p>
          <p class="font-mono text-2xl font-bold text-[#4F8EF7]">{{ allTransactions.length }}</p>
          <p class="text-[11px] text-[#4A6080] mt-1">transações</p>
        </div>
        <div class="relative bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-4 overflow-hidden">
          <div class="absolute bottom-0 left-0 right-0 h-[3px] rounded-b-2xl bg-[#00E5A0]" />
          <p class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080] mb-2">Receitas</p>
          <p class="font-mono text-2xl font-bold text-[#00E5A0]">R$ {{ fmt(totalIncome) }}</p>
          <p class="text-[11px] text-[#4A6080] mt-1">no período</p>
        </div>
        <div class="relative bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-4 overflow-hidden">
          <div class="absolute bottom-0 left-0 right-0 h-[3px] rounded-b-2xl bg-[#FF3D6B]" />
          <p class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080] mb-2">Despesas</p>
          <p class="font-mono text-2xl font-bold text-[#FF3D6B]">R$ {{ fmt(totalExpense) }}</p>
          <p class="text-[11px] text-[#4A6080] mt-1">no período</p>
        </div>
        <div class="relative bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-4 overflow-hidden">
          <div class="absolute bottom-0 left-0 right-0 h-[3px] rounded-b-2xl" :class="netBalance >= 0 ? 'bg-[#00E5A0]' : 'bg-[#FF3D6B]'" />
          <p class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080] mb-2">Saldo</p>
          <p class="font-mono text-2xl font-bold" :class="netBalance >= 0 ? 'text-[#00E5A0]' : 'text-[#FF3D6B]'">R$ {{ fmt(Math.abs(netBalance)) }}</p>
          <p class="text-[11px] text-[#4A6080] mt-1">{{ netBalance >= 0 ? 'positivo' : 'negativo' }}</p>
        </div>
      </section>

      <!-- Filters bar -->
      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-4 flex flex-wrap gap-3 items-end">
        <!-- Search -->
        <div class="flex flex-col gap-1.5 min-w-[180px] flex-1">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Buscar</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#4A6080] text-base pointer-events-none">⌕</span>
            <input
              v-model="filters.search"
              placeholder="Descrição..."
              class="w-full bg-[#07090F] border border-[#1E2D45] text-white text-[13px] placeholder-[#4A6080] pl-8 pr-3 py-2 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
            />
          </div>
        </div>

        <!-- Type -->
        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Tipo</label>
          <select v-model="filters.type" class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none cursor-pointer">
            <option value="">Todos</option>
            <option value="income">Receitas</option>
            <option value="expense">Despesas</option>
          </select>
        </div>

        <!-- Category -->
        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Categoria</label>
          <select v-model="filters.categoryId" class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none cursor-pointer">
            <option value="">Todas</option>
            <option v-for="c in categories" :key="c.id" :value="c.id" class="bg-[#0D1526]">{{ c.name }}</option>
          </select>
        </div>

        <!-- Payment method -->
        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Pagamento</label>
          <select v-model="filters.paymentMethod" class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none cursor-pointer">
            <option value="">Todos</option>
            <option value="pix">Pix</option>
            <option value="credit_card">Cartão de Crédito</option>
            <option value="money">Dinheiro</option>
            <option value="others">Outros</option>
          </select>
        </div>

        <!-- Date from -->
        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">De</label>
          <input
            v-model="filters.dateFrom"
            type="date"
            class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors [color-scheme:dark]"
          />
        </div>

        <!-- Date to -->
        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Até</label>
          <input
            v-model="filters.dateTo"
            type="date"
            class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors [color-scheme:dark]"
          />
        </div>

        <!-- Clear filters -->
        <button
          v-if="hasActiveFilters"
          class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-[#FF3D6B]/30 text-[12px] font-semibold text-[#FF3D6B] hover:bg-[#FF3D6B]/10 transition-all self-end"
          @click="clearFilters"
        >✕ Limpar</button>
      </div>

      <!-- Table -->
      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl overflow-hidden">
        <!-- Table header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2D45]">
          <div class="flex items-center gap-3">
            <h4 class="text-sm font-bold text-white">Resultados</h4>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-white/[0.06] text-[#4A6080]">
              {{ filteredTransactions.length }} encontradas
            </span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm border-collapse">
            <thead>
              <tr class="bg-black/20">
                <th class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Descrição</th>
                <th class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Categoria</th>
                <th class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Pagamento</th>
                <th class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Data</th>
                <th class="px-5 py-3 text-right text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Valor</th>
                <th class="px-5 py-3 text-right text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="px-5 py-12 text-center text-[#4A6080] text-sm">Carregando...</td>
              </tr>
              <tr v-else-if="paginatedTransactions.length === 0">
                <td colspan="6" class="px-5 py-12 text-center text-[#4A6080] text-sm">Nenhuma transação encontrada</td>
              </tr>
              <tr
                v-for="t in paginatedTransactions"
                :key="t.id"
                class="border-b border-[#1E2D45] last:border-0 hover:bg-white/[0.02] transition-colors"
              >
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full shrink-0"
                      :class="t.category.type === 'income' ? 'bg-[#00E5A0] shadow-[0_0_6px_#00E5A0]' : 'bg-[#FF3D6B] shadow-[0_0_6px_#FF3D6B]'"
                    />
                    <span class="text-white font-medium text-[13px]">{{ t.description }}</span>
                    <span v-if="t.is_recurring" class="text-[10px] text-[#4F8EF7] bg-[#4F8EF7]/12 px-1.5 py-0.5 rounded font-semibold">↻</span>
                  </div>
                </td>
                <td class="px-5 py-3.5">
                  <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full"
                    :class="t.category.type === 'income' ? 'bg-[#00E5A0]/10 text-[#00E5A0]' : 'bg-[#FF3D6B]/10 text-[#FF3D6B]'">
                    {{ t.category.name }}
                  </span>
                </td>
                <td class="px-5 py-3.5">
                  <span class="flex items-center gap-1.5 text-[12px] text-[#4A6080]">
                    {{ paymentIcon(t.payment_method) }} {{ paymentLabel(t.payment_method) }}
                  </span>
                </td>
                <td class="px-5 py-3.5 text-[12px] text-[#4A6080]">{{ formatDate(t.transaction_date) }}</td>
                <td class="px-5 py-3.5 text-right">
                  <span class="font-mono text-[13px] font-bold"
                    :class="t.category.type === 'income' ? 'text-[#00E5A0]' : 'text-[#FF3D6B]'">
                    {{ t.category.type === 'income' ? '+' : '−' }} R$ {{ fmt(Math.abs(Number(t.amount))) }}
                  </span>
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex gap-1.5 justify-end">
                    <button
                      class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#1E2D45] text-[#4A6080] hover:border-[#4F8EF7] hover:text-[#4F8EF7] hover:bg-[#4F8EF7]/10 transition-all text-[13px]"
                      @click="openModal(t)"
                    >✎</button>
                    <button
                      class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#1E2D45] text-[#4A6080] hover:border-[#FF3D6B] hover:text-[#FF3D6B] hover:bg-[#FF3D6B]/10 transition-all text-[13px]"
                      @click="openDeleteModal(t)"
                    >✕</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-3.5 border-t border-[#1E2D45]">
          <span class="text-[12px] text-[#4A6080]">
            Mostrando {{ paginationFrom }}–{{ paginationTo }} de {{ filteredTransactions.length }}
          </span>
          <div class="flex gap-2">
            <button
              :disabled="currentPage === 1"
              class="px-4 py-1.5 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-white text-[12px] font-semibold hover:bg-[#4F8EF7] hover:border-[#4F8EF7] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
              @click="currentPage--"
            >‹ Anterior</button>
            <button
              :disabled="currentPage === lastPage"
              class="px-4 py-1.5 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-white text-[12px] font-semibold hover:bg-[#4F8EF7] hover:border-[#4F8EF7] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
              @click="currentPage++"
            >Próxima ›</button>
          </div>
        </div>
      </div>

    </div>

    <!-- Modals -->
    <TransactionModal
      :isOpen="isModalOpen"
      :isEditing="isEditing"
      :categories="categories"
      :initial="editingForm"
      @close="closeModal"
      @submit="submitTransaction"
    />
    <DeleteModal
      :isOpen="isDeleteModalOpen"
      :transactionName="deleteTarget?.description"
      @close="isDeleteModalOpen = false"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import type { Transaction, Category, TransactionForm } from '@/types/finance'
import AppSidebar       from '@/components/dashboard/AppSidebar.vue'
import TransactionModal from '@/components/modals/TransactionModal.vue'
import DeleteModal      from '@/components/modals/DeleteModal.vue'

// ── State ──────────────────────────────────────────────────────────────────
const isMenuOpen        = ref(false)
const isModalOpen       = ref(false)
const isEditing         = ref(false)
const editingId         = ref<number | null>(null)
const editingForm       = ref<Partial<TransactionForm>>({})
const isDeleteModalOpen = ref(false)
const deleteTarget      = ref<Transaction | null>(null)
const loading           = ref(false)
const allTransactions   = ref<Transaction[]>([])
const categories        = ref<Category[]>([])
const currentPage       = ref(1)
const perPage           = 15

const filters = ref({
  search:        '',
  type:          '',
  categoryId:    '' as number | '',
  paymentMethod: '',
  dateFrom:      '',
  dateTo:        '',
})

// ── Computed ───────────────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
  Object.values(filters.value).some(v => v !== '')
)

const filteredTransactions = computed(() => {
  let list = allTransactions.value

  if (filters.value.search)
    list = list.filter(t => t.description.toLowerCase().includes(filters.value.search.toLowerCase()))
  if (filters.value.type)
    list = list.filter(t => t.category.type === filters.value.type)
  if (filters.value.categoryId)
    list = list.filter(t => t.category_id === Number(filters.value.categoryId))
  if (filters.value.paymentMethod)
    list = list.filter(t => t.payment_method === filters.value.paymentMethod)
  if (filters.value.dateFrom)
    list = list.filter(t => t.transaction_date >= filters.value.dateFrom)
  if (filters.value.dateTo)
    list = list.filter(t => t.transaction_date <= filters.value.dateTo)

  return list
})

const lastPage        = computed(() => Math.max(1, Math.ceil(filteredTransactions.value.length / perPage)))
const paginationFrom  = computed(() => filteredTransactions.value.length === 0 ? 0 : (currentPage.value - 1) * perPage + 1)
const paginationTo    = computed(() => Math.min(currentPage.value * perPage, filteredTransactions.value.length))
const paginatedTransactions = computed(() =>
  filteredTransactions.value.slice((currentPage.value - 1) * perPage, currentPage.value * perPage)
)

const totalIncome  = computed(() => allTransactions.value.filter(t => t.category.type === 'income').reduce((s, t) => s + Number(t.amount), 0))
const totalExpense = computed(() => allTransactions.value.filter(t => t.category.type === 'expense').reduce((s, t) => s + Number(t.amount), 0))
const netBalance   = computed(() => totalIncome.value - totalExpense.value)

// Reset page when filters change
watch(filters, () => { currentPage.value = 1 }, { deep: true })

// ── API ────────────────────────────────────────────────────────────────────
const fetchAll = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/transactions', { params: { per_page: 9999 } })
    allTransactions.value = data.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

const fetchCategories = async () => {
  try { const { data } = await api.get('/categories'); categories.value = data } catch (e) { console.error(e) }
}

const submitTransaction = async (form: TransactionForm) => {
  try {
    if (isEditing.value) await api.put(`/transactions/${editingId.value}`, form)
    else await api.post('/transactions', form)
    closeModal()
    await fetchAll()
  } catch (e) { console.error(e) }
}

const confirmDelete = async () => {
  if (!deleteTarget.value) return
  try {
    await api.delete(`/transactions/${deleteTarget.value.id}`)
    isDeleteModalOpen.value = false
    deleteTarget.value = null
    await fetchAll()
  } catch (e) { console.error(e) }
}

// ── Helpers ────────────────────────────────────────────────────────────────
const openModal = (t?: Transaction) => {
  if (t) {
    isEditing.value = true; editingId.value = t.id
    editingForm.value = { category_id: t.category_id, description: t.description, amount: t.amount, transaction_date: t.transaction_date, payment_method: t.payment_method, is_recurring: t.is_recurring, recurrence_type: t.recurrence_type }
  } else {
    isEditing.value = false; editingId.value = null; editingForm.value = {}
  }
  isModalOpen.value = true
}
const closeModal       = () => { isModalOpen.value = false; isEditing.value = false; editingId.value = null; editingForm.value = {} }
const openDeleteModal  = (t: Transaction) => { deleteTarget.value = t; isDeleteModalOpen.value = true }
const clearFilters     = () => { filters.value = { search: '', type: '', categoryId: '', paymentMethod: '', dateFrom: '', dateTo: '' } }

const fmt         = (v: number) => new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2 }).format(v)
const formatDate  = (d: string) => new Date(d).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
const paymentIcon = (m: string) => ({ pix: '⚡', credit_card: '💳', money: '💵', others: '◦' }[m] ?? '◦')
const paymentLabel= (m: string) => ({ pix: 'Pix', credit_card: 'Crédito', money: 'Dinheiro', others: 'Outros' }[m] ?? m)

onMounted(async () => { await fetchAll(); await fetchCategories() })
</script>
