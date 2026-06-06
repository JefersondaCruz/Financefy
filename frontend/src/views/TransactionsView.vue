<template>
  <div class="min-h-screen bg-[#07090F] text-white flex">

    <AppSidebar :isOpen="isMenuOpen" activeItem="Transações" @close="isMenuOpen = false" />

    <div class="flex-1 flex flex-col gap-6 px-6 py-6 md:px-8 min-w-0">

      <AppPageHeader
        title="Transações"
        subtitle="Histórico completo de movimentações"
        @open-menu="isMenuOpen = true"
      >
        <template #actions>
        <button
          class="flex items-center gap-2 border border-[#1E2D45] bg-white/[0.04] hover:border-[#00E5A0]/60 hover:bg-[#00E5A0]/10 text-[#00E5A0] text-[13px] font-bold px-4 py-2.5 rounded-xl transition-all hover:-translate-y-px whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed"
          :disabled="totalTransactions === 0"
          @click="exportCsv"
        >
          ⇩ Exportar CSV
        </button>
        <button
          class="flex items-center gap-2 bg-[#4F8EF7] hover:bg-[#3a7de0] text-white text-[13px] font-bold px-4 py-2.5 rounded-xl transition-all hover:-translate-y-px whitespace-nowrap"
          @click="openModal()"
        >
          <span class="text-lg font-light leading-none">+</span>
          Nova Transação
        </button>
        </template>
      </AppPageHeader>

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

      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-4 flex flex-wrap gap-3 items-end">
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

        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Tipo</label>
          <select v-model="filters.type" class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none cursor-pointer">
            <option value="">Todos</option>
            <option value="income">Receitas</option>
            <option value="expense">Despesas</option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Categoria</label>
          <select v-model="filters.categoryId" class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none cursor-pointer">
            <option value="">Todas</option>
            <option v-for="c in categories" :key="c.id" :value="c.id" class="bg-[#0D1526]">{{ c.name }}</option>
          </select>
        </div>

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

        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Recorrência</label>
          <select v-model="filters.recurring" class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none cursor-pointer">
            <option value="">Todas</option>
            <option value="yes">Recorrentes</option>
            <option value="no">Não recorrentes</option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">De</label>
          <input
            v-model="filters.dateFrom"
            type="date"
            class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors [color-scheme:dark]"
          />
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080]">Até</label>
          <input
            v-model="filters.dateTo"
            type="date"
            class="bg-[#07090F] border border-[#1E2D45] text-white text-[13px] px-3 py-2 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors [color-scheme:dark]"
          />
        </div>

        <button
          v-if="hasActiveFilters"
          class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-[#FF3D6B]/30 text-[12px] font-semibold text-[#FF3D6B] hover:bg-[#FF3D6B]/10 transition-all self-end"
          @click="clearFilters"
        >✕ Limpar</button>

        <div class="flex flex-wrap gap-2 basis-full pt-1">
          <button
            v-for="shortcut in periodShortcuts"
            :key="shortcut.key"
            class="px-3 py-1.5 rounded-lg border border-[#1E2D45] bg-white/[0.03] text-[12px] font-semibold text-[#8BA3C0] hover:border-[#4F8EF7]/60 hover:text-white transition-colors"
            @click="applyPeriodShortcut(shortcut.key)"
          >
            {{ shortcut.label }}
          </button>
        </div>
      </div>

      <AppAlert
        v-if="errorMessage"
        :message="errorMessage"
        variant="error"
        :action-label="canRetryLoad ? 'Tentar novamente' : ''"
        @action="loadData"
      />

      <AppAlert
        v-if="successMessage"
        :message="successMessage"
        variant="success"
      />

      <TransactionsTable
        title="Resultados"
        :transactions="allTransactions"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="totalTransactions"
        :loading="loading"
        loading-label="Carregando..."
        :error-message="tableLoadError"
        error-action-label="Tentar novamente"
        :empty-title="emptyTitle"
        :empty-description="emptyDescription"
        :empty-action-label="emptyActionLabel"
        :show-filters="false"
        show-payment-method
        :total-label="`${totalTransactions} encontradas`"
        :pagination-label="paginationLabel"
        @edit="openModal"
        @duplicate="duplicateTransaction"
        @delete="openDeleteModal"
        @prev-page="goPrevPage"
        @next-page="goNextPage"
        @empty-action="emptyAction"
        @error-action="loadData"
      />

    </div>

    <TransactionModal
      :isOpen="isModalOpen"
      :isEditing="isEditing"
      :categories="categories"
      :initial="editingForm"
      :saving="savingTransaction"
      @close="closeModal"
      @submit="submitTransaction"
    />
    <DeleteModal
      :isOpen="isDeleteModalOpen"
      :transactionName="deleteTarget?.description"
      :loading="deletingTransaction"
      :error-message="deleteErrorMessage"
      @close="closeDeleteModal"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { categoryService } from '@/services/categoryService'
import { transactionService } from '@/services/transactionService'
import type { Transaction, Category, TransactionForm, TransactionSummary } from '@/types/finance'
import { formatCurrency } from '@/utils/formatters'
import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import AppPageHeader from '@/components/dashboard/AppPageHeader.vue'
import TransactionsTable from '@/components/dashboard/TransactionsTable.vue'
import TransactionModal from '@/components/modals/TransactionModal.vue'
import DeleteModal from '@/components/modals/DeleteModal.vue'
import AppAlert from '@/components/AppAlert.vue'

type TransactionFilters = {
  search: string
  type: string
  categoryId: number | ''
  paymentMethod: string
  recurring: string
  dateFrom: string
  dateTo: string
}

type PeriodShortcutKey = 'today' | 'current-month' | 'previous-month' | 'last-30-days'
const isMenuOpen = ref(false)
const isModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const editingForm = ref<Partial<TransactionForm>>({})
const isDeleteModalOpen = ref(false)
const deleteTarget = ref<Transaction | null>(null)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const canRetryLoad = ref(false)
const transactionsErrorMessage = ref('')
const savingTransaction = ref(false)
const deletingTransaction = ref(false)
const deleteErrorMessage = ref('')
const allTransactions = ref<Transaction[]>([])
const categories = ref<Category[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const totalTransactions = ref(0)
const perPage = 15

const emptySummary = (): TransactionSummary => ({
  transaction_count: 0,
  total_income: 0,
  total_expenses: 0,
  net_balance: 0,
  monthly_average: 0,
  recurring_count: 0,
  category_ranking: [],
})
const summary = ref<TransactionSummary>(emptySummary())

const defaultFilters = (): TransactionFilters => ({
  search: '',
  type: '',
  categoryId: '' as number | '',
  paymentMethod: '',
  recurring: '',
  dateFrom: '',
  dateTo: '',
})

const periodShortcuts: Array<{ key: PeriodShortcutKey; label: string }> = [
  { key: 'today', label: 'Hoje' },
  { key: 'current-month', label: 'Este mês' },
  { key: 'previous-month', label: 'Mês anterior' },
  { key: 'last-30-days', label: 'Últimos 30 dias' },
]

const filters = ref<TransactionFilters>(defaultFilters())

const hasActiveFilters = computed(() =>
  Object.values(filters.value).some(v => v !== '')
)

const paginationFrom  = computed(() => totalTransactions.value === 0 ? 0 : (currentPage.value - 1) * perPage + 1)
const paginationTo    = computed(() => Math.min(currentPage.value * perPage, totalTransactions.value))
const paginationLabel = computed(() =>
  `Mostrando ${paginationFrom.value}-${paginationTo.value} de ${totalTransactions.value}`
)
const tableLoadError = computed(() =>
  allTransactions.value.length === 0 ? transactionsErrorMessage.value : ''
)

const emptyTitle = computed(() =>
  totalTransactions.value === 0 && !hasActiveFilters.value
    ? 'Nenhuma transação cadastrada'
    : 'Nenhum resultado para os filtros'
)

const emptyDescription = computed(() =>
  totalTransactions.value === 0 && !hasActiveFilters.value
    ? 'Cadastre sua primeira transação para começar a acompanhar receitas e despesas.'
    : 'Ajuste os filtros ou limpe a busca para ver outras transações.'
)

const emptyActionLabel = computed(() =>
  totalTransactions.value === 0 && !hasActiveFilters.value ? 'Criar primeira transação' : 'Limpar filtros'
)

const totalIncome  = computed(() => summary.value.total_income)
const totalExpense = computed(() => summary.value.total_expenses)
const netBalance   = computed(() => summary.value.net_balance)

watch(filters, () => {
  fetchAll(1)
  fetchSummary()
}, { deep: true })

const requestParams = (page = currentPage.value) => {
  const params = {
    page,
    per_page: perPage,
    start_date: filters.value.dateFrom || '1900-01-01',
    end_date: filters.value.dateTo || '2100-12-31',
  } as const

  return {
    ...params,
    ...(filters.value.search ? { search: filters.value.search } : {}),
    ...(filters.value.type ? { type: filters.value.type } : {}),
    ...(filters.value.categoryId ? { category_id: Number(filters.value.categoryId) } : {}),
    ...(filters.value.paymentMethod ? { payment_method: filters.value.paymentMethod } : {}),
    ...(filters.value.recurring ? { recurring: filters.value.recurring } : {}),
  }
}

const fetchAll = async (page = currentPage.value) => {
  loading.value = true
  errorMessage.value = ''
  transactionsErrorMessage.value = ''
  canRetryLoad.value = false
  try {
    const response = await transactionService.list(requestParams(page))
    allTransactions.value = response.data
    currentPage.value = response.current_page
    lastPage.value = response.last_page
    totalTransactions.value = response.total
  } catch {
    errorMessage.value = 'Não foi possível carregar as transações.'
    transactionsErrorMessage.value = errorMessage.value
    canRetryLoad.value = true
  } finally {
    loading.value = false
  }
}

const fetchSummary = async () => {
  try {
    summary.value = await transactionService.summary(requestParams(1))
  } catch {
    errorMessage.value = 'Não foi possível carregar o resumo das transações.'
    canRetryLoad.value = true
  }
}

const fetchCategories = async () => {
  try {
    categories.value = await categoryService.list()
  } catch {
    errorMessage.value = 'Não foi possível carregar as categorias.'
    canRetryLoad.value = true
  }
}

const loadData = async () => {
  await Promise.all([fetchAll(), fetchSummary(), fetchCategories()])
}

const submitTransaction = async (form: TransactionForm) => {
  errorMessage.value = ''
  successMessage.value = ''
  savingTransaction.value = true
  try {
    const wasEditing = isEditing.value && editingId.value
    if (wasEditing) await transactionService.update(editingId.value!, form)
    else await transactionService.create(form)
    closeModal()
    await Promise.all([fetchAll(currentPage.value), fetchSummary()])
    successMessage.value = wasEditing ? 'Transação atualizada com sucesso.' : 'Transação criada com sucesso.'
  } catch {
    errorMessage.value = 'Não foi possível salvar a transação.'
    canRetryLoad.value = false
  } finally {
    savingTransaction.value = false
  }
}

const confirmDelete = async () => {
  if (!deleteTarget.value) return
  errorMessage.value = ''
  deleteErrorMessage.value = ''
  successMessage.value = ''
  deletingTransaction.value = true
  try {
    await transactionService.remove(deleteTarget.value.id)
    resetDeleteModal()
    await Promise.all([fetchAll(currentPage.value), fetchSummary()])
    successMessage.value = 'Transação excluída com sucesso.'
  } catch {
    deleteErrorMessage.value = 'Não foi possível excluir a transação.'
  } finally {
    deletingTransaction.value = false
  }
}

const openModal = (t?: Transaction) => {
  if (t) {
    isEditing.value = true
    editingId.value = t.id
    editingForm.value = {
      category_id: t.category_id,
      description: t.description,
      amount: t.amount,
      transaction_date: t.transaction_date,
      payment_method: t.payment_method,
      is_recurring: t.is_recurring,
      recurrence_type: t.recurrence_type,
    }
  } else {
    isEditing.value = false
    editingId.value = null
    editingForm.value = {}
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  isEditing.value = false
  editingId.value = null
  editingForm.value = {}
}

const openDeleteModal = (t: Transaction) => {
  deleteTarget.value = t
  deleteErrorMessage.value = ''
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (deletingTransaction.value) return
  resetDeleteModal()
}

const resetDeleteModal = () => {
  isDeleteModalOpen.value = false
  deleteTarget.value = null
  deleteErrorMessage.value = ''
}

const clearFilters = () => {
  filters.value = defaultFilters()
}

const toISODate = (date: Date) => {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const applyPeriodShortcut = (key: PeriodShortcutKey) => {
  const today = new Date()
  let start = new Date(today)
  let end = new Date(today)

  if (key === 'current-month') {
    start = new Date(today.getFullYear(), today.getMonth(), 1)
    end = new Date(today.getFullYear(), today.getMonth() + 1, 0)
  }

  if (key === 'previous-month') {
    start = new Date(today.getFullYear(), today.getMonth() - 1, 1)
    end = new Date(today.getFullYear(), today.getMonth(), 0)
  }

  if (key === 'last-30-days') {
    start = new Date(today)
    start.setDate(today.getDate() - 29)
  }

  filters.value.dateFrom = toISODate(start)
  filters.value.dateTo = toISODate(end)
}

const exportCsv = async () => {
  const blob = await transactionService.exportCsv(requestParams(1))
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `transacoes-${toISODate(new Date())}.csv`
  link.click()
  URL.revokeObjectURL(url)
}

const duplicateTransaction = (t: Transaction) => {
  isEditing.value = false
  editingId.value = null
  editingForm.value = {
    category_id: t.category_id,
    description: `Cópia de ${t.description}`,
    amount: t.amount,
    transaction_date: t.transaction_date,
    payment_method: t.payment_method,
    is_recurring: t.is_recurring,
    recurrence_type: t.recurrence_type,
  }
  isModalOpen.value = true
}

const goPrevPage = () => {
  if (currentPage.value > 1) fetchAll(currentPage.value - 1)
}

const goNextPage = () => {
  if (currentPage.value < lastPage.value) fetchAll(currentPage.value + 1)
}

const emptyAction = () => {
  if (allTransactions.value.length === 0) openModal()
  else clearFilters()
}

const fmt = formatCurrency

onMounted(async () => {
  await loadData()
})
</script>
