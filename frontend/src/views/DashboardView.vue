<template>
  <div class="min-h-screen bg-[#07090F] text-white flex">

    <AppSidebar :isOpen="isMenuOpen" activeItem="Dashboard" @close="isMenuOpen = false" />

    <div class="flex-1 flex flex-col gap-6 px-6 py-6 md:px-8 min-w-0">

      <AppPageHeader
        title="Dashboard"
        :subtitle="`${greeting}, bem-vindo de volta.`"
        @open-menu="isMenuOpen = true"
      >
        <template #actions>
          <DateRangeFilter @change="onDateChange" />
          <button
            :disabled="loadingCategories"
            class="flex items-center gap-2 bg-[#4F8EF7] hover:bg-[#3a7de0] text-white text-[13px] font-bold px-4 py-2.5 rounded-xl transition-colors hover:-translate-y-px duration-150 whitespace-nowrap"
            @click="openModal"
          >
            <span class="text-lg font-light leading-none">+</span>
            {{ loadingCategories ? 'Carregando categorias...' : 'Nova Transação' }}
          </button>
        </template>
      </AppPageHeader>

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

      <section v-if="loadingTransactions" class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div
          v-for="label in ['Despesas Totais', 'Receitas Totais', 'Saldo Líquido']"
          :key="label"
          class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-6 text-sm text-[#4A6080]"
        >
          Carregando {{ label.toLowerCase() }}...
        </div>
      </section>

      <section v-else class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <KpiCard label="Despesas Totais" icon="📉" :value="totalExpenses" :trend="12"  variant="expense" />
        <KpiCard label="Receitas Totais" icon="📈" :value="totalIncome"   :trend="8"   variant="income"  />
        <KpiCard label="Saldo Líquido"   icon="💰" :value="netBalance"    :trend="netBalance >= 0 ? 5 : -5" variant="balance" />
      </section>

      <section class="grid grid-cols-1 xl:grid-cols-[1fr_1.1fr] gap-5">
        <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <div>
              <h4 class="text-sm font-bold text-white">Resumo mensal</h4>
              <p class="text-[12px] text-[#4A6080]">{{ selectedPeriodLabel }}</p>
            </div>
            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-[#4F8EF7]/12 text-[#4F8EF7]">
              {{ summary.transaction_count }} transações
            </span>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="rounded-xl border border-[#1E2D45] bg-white/[0.03] p-3">
              <p class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080] mb-1">Receitas</p>
              <p class="font-mono text-[15px] font-bold text-[#00E5A0]">R$ {{ fmt(totalIncome) }}</p>
            </div>
            <div class="rounded-xl border border-[#1E2D45] bg-white/[0.03] p-3">
              <p class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080] mb-1">Despesas</p>
              <p class="font-mono text-[15px] font-bold text-[#FF3D6B]">R$ {{ fmt(totalExpenses) }}</p>
            </div>
            <div class="rounded-xl border border-[#1E2D45] bg-white/[0.03] p-3">
              <p class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080] mb-1">Ticket médio</p>
              <p class="font-mono text-[15px] font-bold text-white">R$ {{ fmt(monthlyAverage) }}</p>
            </div>
            <div class="rounded-xl border border-[#1E2D45] bg-white/[0.03] p-3">
              <p class="text-[10px] font-bold tracking-widest uppercase text-[#4A6080] mb-1">Recorrentes</p>
              <p class="font-mono text-[15px] font-bold text-[#4F8EF7]">{{ recurringMonthlyCount }}</p>
            </div>
          </div>
        </div>

        <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <div>
              <h4 class="text-sm font-bold text-white">Ranking de categorias</h4>
              <p class="text-[12px] text-[#4A6080]">Despesas do mês por categoria</p>
            </div>
          </div>

          <div v-if="categoryRanking.length === 0" class="py-8 text-center text-[13px] text-[#4A6080]">
            Nenhuma despesa no período.
          </div>
          <div v-else class="flex flex-col gap-3">
            <div v-for="item in categoryRanking" :key="item.name" class="flex items-center gap-3">
              <div class="w-28 truncate text-[12px] font-semibold text-[#E8EEFF]">{{ item.name }}</div>
              <div class="h-2 flex-1 rounded-full bg-white/[0.06] overflow-hidden">
                <div class="h-full rounded-full bg-[#FF3D6B]" :style="{ width: `${item.percent}%` }" />
              </div>
              <div class="w-24 text-right font-mono text-[12px] font-bold text-[#FF3D6B]">R$ {{ fmt(item.total) }}</div>
            </div>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-1 lg:grid-cols-[1fr_1.4fr] gap-5">
        <CategoryChart
          :transactions="transactions"
          :loading="loadingTransactions"
          :error-message="transactionsStateError"
        />
        <TrendChart
          :transactions="transactions"
          :loading="loadingTransactions"
          :error-message="transactionsStateError"
        />
      </section>

    <div
      class="bg-[#0D1526] border border-[#4F8EF7]/25 rounded-2xl p-6 flex flex-col gap-5"
    >
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 shrink-0 bg-[#4F8EF7]/12 border border-[#4F8EF7]/25 rounded-[14px] flex items-center justify-center text-[22px]">
          ◈
        </div>
        <div>
          <div class="flex items-center gap-2 mb-1">
            <p class="text-[15px] font-bold text-[#E8EEFF]">Converse com sua IA Financeira</p>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#4F8EF7]/15 text-[#4F8EF7] tracking-widest uppercase">
              Beta
            </span>
          </div>
          <p class="text-[13px] text-[#4A6080] leading-relaxed">
            Analise seus gastos, identifique padrões e receba dicas personalizadas com base nos seus dados reais.
            A IA tem acesso ao seu histórico dos últimos 3 meses.
          </p>
        </div>
      </div>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="chip in iaChips"
          :key="chip"
          class="px-3 py-1.5 rounded-full bg-white/[0.04] border border-[#1E2D45] text-[12px] text-[#4A6080]"
        >
          {{ chip }}
        </span>
      </div>

      <div class="flex items-center justify-between border-t border-[#1E2D45] pt-4 flex-wrap gap-3">
        <p class="text-[12px] text-[#4A6080] flex items-center gap-1.5">
          <span class="text-sm">ℹ️</span>
          A IA analisa e aconselha — o cadastro de transações é feito manualmente por enquanto.
        </p>
        <RouterLink
          to="/ai"
          class="flex items-center gap-2 bg-[#4F8EF7] hover:bg-[#3a7de0] text-white text-[13px] font-bold px-4 py-2.5 rounded-xl transition-colors hover:-translate-y-px duration-150"
        >
          Abrir IA Financeira
          <span class="text-base leading-none">→</span>
        </RouterLink>
      </div>
    </div>

      <TransactionsTable
        :transactions="filteredTransactions"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="total"
        :loading="loadingTransactions"
        :error-message="transactionsStateError"
        error-action-label="Tentar novamente"
        :empty-title="transactionsEmptyTitle"
        :empty-description="transactionsEmptyDescription"
        :empty-action-label="transactionsEmptyActionLabel"
        @edit="openEditModal"
        @duplicate="duplicateTransaction"
        @delete="openDeleteModal"
        @next-page="nextPage"
        @prev-page="prevPage"
        @search="q => (searchQuery = q)"
        @filter-type="v => (typeFilter = v)"
        @empty-action="onTransactionsEmptyAction"
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
import { ref, computed, onMounted } from 'vue'
import { dashboardService } from '@/services/dashboardService'

import type { Transaction, Category, TransactionForm, DateFilter, TransactionSummary } from '@/types/finance'
import { formatCurrency, formatDate } from '@/utils/formatters'

import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import AppPageHeader from '@/components/dashboard/AppPageHeader.vue'
import DateRangeFilter from '@/components/dashboard/DateRangeFilter.vue'
import KpiCard from '@/components/dashboard/KpiCard.vue'
import CategoryChart from '@/components/dashboard/CategoryChart.vue'
import TrendChart from '@/components/dashboard/TrendChart.vue'
import TransactionsTable from '@/components/dashboard/TransactionsTable.vue'
import TransactionModal from '@/components/modals/TransactionModal.vue'
import DeleteModal from '@/components/modals/DeleteModal.vue'
import AppAlert from '@/components/AppAlert.vue'

const isMenuOpen = ref(false)
const isModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const editingForm = ref<Partial<TransactionForm>>({})
const isDeleteModalOpen = ref(false)
const deleteTarget = ref<Transaction | null>(null)
const errorMessage = ref('')
const successMessage = ref('')
const canRetryLoad = ref(false)
const loadingTransactions = ref(false)
const loadingCategories = ref(false)
const savingTransaction = ref(false)
const deletingTransaction = ref(false)
const deleteErrorMessage = ref('')
const transactionsErrorMessage = ref('')

const emptySummary = (): TransactionSummary => ({
  transaction_count: 0,
  total_income: 0,
  total_expenses: 0,
  net_balance: 0,
  monthly_average: 0,
  recurring_count: 0,
  category_ranking: [],
})

const transactions = ref<Transaction[]>([])
const summary = ref<TransactionSummary>(emptySummary())
const categories = ref<Category[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const perPage = ref(10)

const searchQuery = ref('')
const typeFilter = ref('')
const now = new Date()
const dateFilter = ref<DateFilter>({
  start_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`,
  end_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()}`,
})
const totalExpenses = computed(() => summary.value.total_expenses)
const totalIncome = computed(() => summary.value.total_income)
const netBalance = computed(() => summary.value.net_balance)
const monthlyAverage = computed(() => summary.value.monthly_average)
const recurringMonthlyCount = computed(() => summary.value.recurring_count)
const selectedPeriodLabel = computed(() =>
  `${formatDateLabel(dateFilter.value.start_date)} a ${formatDateLabel(dateFilter.value.end_date)}`
)
const categoryRanking = computed(() => {
  const highest = Math.max(...summary.value.category_ranking.map(item => item.total), 0)
  return summary.value.category_ranking.map(({ name, total }) => ({
      name,
      total,
      percent: highest > 0 ? Math.max(6, Math.round((total / highest) * 100)) : 0,
    })
  )
})

const iaChips = [
  '"Onde estou gastando mais?"',
  '"Como está minha taxa de poupança?"',
  '"Dicas para economizar"',
]

const filteredTransactions = computed(() => {
  let list = transactions.value
  if (searchQuery.value)
    list = list.filter(t => t.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  if (typeFilter.value)
    list = list.filter(t => t.category?.type === typeFilter.value)
  return list
})

const hasTableFilters = computed(() => searchQuery.value !== '' || typeFilter.value !== '')

const transactionsEmptyTitle = computed(() =>
  total.value === 0
    ? 'Nenhuma transação cadastrada'
    : 'Nenhum resultado para os filtros'
)

const transactionsEmptyDescription = computed(() =>
  total.value === 0
    ? 'Cadastre sua primeira transação para acompanhar seu fluxo financeiro no dashboard.'
    : 'Ajuste a busca ou o tipo para localizar outras transações.'
)

const transactionsEmptyActionLabel = computed(() =>
  hasTableFilters.value ? 'Limpar filtros' : 'Criar primeira transação'
)

const transactionsStateError = computed(() =>
  transactions.value.length === 0 ? transactionsErrorMessage.value : ''
)

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Bom dia'
  if (h < 18) return 'Boa tarde'
  return 'Boa noite'
})

const fetchTransactions = async (page = 1) => {
  loadingTransactions.value = true
  errorMessage.value = ''
  transactionsErrorMessage.value = ''
  canRetryLoad.value = false
  try {
    const data = await dashboardService.getTransactions({
      page,
      per_page: perPage.value,
      start_date: dateFilter.value.start_date,
      end_date: dateFilter.value.end_date,
    })
    transactions.value = data.data
    currentPage.value = data.current_page
    lastPage.value = data.last_page
    total.value = data.total
  } catch {
    errorMessage.value = 'Não foi possível carregar as transações.'
    transactionsErrorMessage.value = errorMessage.value
    canRetryLoad.value = true
  } finally {
    loadingTransactions.value = false
  }
}

const fetchTransactionSummary = async () => {
  try {
    summary.value = await dashboardService.getTransactionSummary({
      start_date: dateFilter.value.start_date,
      end_date: dateFilter.value.end_date,
    })
  } catch {
    errorMessage.value = 'Não foi possível carregar o resumo mensal.'
    canRetryLoad.value = true
  }
}

const fetchCategories = async () => {
  loadingCategories.value = true
  try {
    categories.value = await dashboardService.getCategories()
  } catch {
    errorMessage.value = 'Não foi possível carregar as categorias.'
    canRetryLoad.value = true
  } finally {
    loadingCategories.value = false
  }
}

const loadData = async () => {
  successMessage.value = ''
  await Promise.all([fetchTransactions(currentPage.value), fetchTransactionSummary(), fetchCategories()])
}

const createTransaction = async (form: TransactionForm) => {
  errorMessage.value = ''
  successMessage.value = ''
  savingTransaction.value = true
  try {
    await dashboardService.createTransaction(form)
    closeModal()
    await Promise.all([fetchTransactions(), fetchTransactionSummary()])
    successMessage.value = 'Transação criada com sucesso.'
  } catch {
    errorMessage.value = 'Não foi possível criar a transação.'
    canRetryLoad.value = false
  } finally {
    savingTransaction.value = false
  }
}

const updateTransaction = async (form: TransactionForm) => {
  if (!editingId.value) return
  errorMessage.value = ''
  successMessage.value = ''
  savingTransaction.value = true
  try {
    await dashboardService.updateTransaction(editingId.value, form)
    closeModal()
    await Promise.all([fetchTransactions(currentPage.value), fetchTransactionSummary()])
    successMessage.value = 'Transação atualizada com sucesso.'
  } catch {
    errorMessage.value = 'Não foi possível atualizar a transação.'
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
    await dashboardService.deleteTransaction(deleteTarget.value.id)
    resetDeleteModal()
    await Promise.all([fetchTransactions(currentPage.value), fetchTransactionSummary()])
    successMessage.value = 'Transação excluída com sucesso.'
  } catch {
    deleteErrorMessage.value = 'Não foi possível excluir a transação.'
  } finally {
    deletingTransaction.value = false
  }
}

const submitTransaction = (form: TransactionForm) =>
  isEditing.value ? updateTransaction(form) : createTransaction(form)

const openModal = () => {
  isEditing.value = false
  editingForm.value = {}
  isModalOpen.value = true
}

const openEditModal = (t: Transaction) => {
  isEditing.value = true
  editingId.value = t.id
  editingForm.value = {
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

const duplicateTransaction = (t: Transaction) => {
  isEditing.value = false
  editingId.value = null
  editingForm.value = {
    category_id: t.category_id,
    description: `Cópia de ${t.description}`,
    amount: t.amount,
    transaction_date: t.transaction_date,
    payment_method: t.payment_method,
    is_recurring: t.is_recurring ?? false,
    recurrence_type: t.recurrence_type ?? null,
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

const onDateChange = (df: DateFilter) => {
  dateFilter.value = df
  Promise.all([fetchTransactions(1), fetchTransactionSummary()])
}
const nextPage = () => {
  if (currentPage.value < lastPage.value) fetchTransactions(currentPage.value + 1)
}
const prevPage = () => {
  if (currentPage.value > 1) fetchTransactions(currentPage.value - 1)
}

const formatDateLabel = (value: string) =>
  formatDate(value, {
    day: '2-digit',
    month: 'short',
  })

const fmt = formatCurrency

const onTransactionsEmptyAction = () => {
  if (hasTableFilters.value) {
    searchQuery.value = ''
    typeFilter.value = ''
  } else {
    openModal()
  }
}

onMounted(async () => {
  await fetchCategories()
})
</script>
