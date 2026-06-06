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
        :transactions="paginatedTransactions"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="filteredTransactions.length"
        :loading="loading"
        loading-label="Carregando..."
        :error-message="tableLoadError"
        error-action-label="Tentar novamente"
        :empty-title="emptyTitle"
        :empty-description="emptyDescription"
        :empty-action-label="emptyActionLabel"
        :show-filters="false"
        show-payment-method
        :total-label="`${filteredTransactions.length} encontradas`"
        :pagination-label="paginationLabel"
        @edit="openModal"
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
import type { Transaction, Category, TransactionForm } from '@/types/finance'
import { formatCurrency } from '@/utils/formatters'
import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import AppPageHeader from '@/components/dashboard/AppPageHeader.vue'
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
const perPage = 15

const filters = ref({
  search: '',
  type: '',
  categoryId: '' as number | '',
  paymentMethod: '',
  dateFrom: '',
  dateTo: '',
})

const hasActiveFilters = computed(() =>
  Object.values(filters.value).some(v => v !== '')
)

const filteredTransactions = computed(() => {
  let list = allTransactions.value

  if (filters.value.search)
    list = list.filter(t => t.description.toLowerCase().includes(filters.value.search.toLowerCase()))
  if (filters.value.type)
    list = list.filter(t => t.category?.type === filters.value.type)
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
const paginationLabel = computed(() =>
  `Mostrando ${paginationFrom.value}-${paginationTo.value} de ${filteredTransactions.value.length}`
)
const tableLoadError = computed(() =>
  allTransactions.value.length === 0 ? transactionsErrorMessage.value : ''
)
const paginatedTransactions = computed(() => {
  return filteredTransactions.value.slice((currentPage.value - 1) * perPage, currentPage.value * perPage)
})

const emptyTitle = computed(() =>
  allTransactions.value.length === 0
    ? 'Nenhuma transação cadastrada'
    : 'Nenhum resultado para os filtros'
)

const emptyDescription = computed(() =>
  allTransactions.value.length === 0
    ? 'Cadastre sua primeira transação para começar a acompanhar receitas e despesas.'
    : 'Ajuste os filtros ou limpe a busca para ver outras transações.'
)

const emptyActionLabel = computed(() =>
  allTransactions.value.length === 0 ? 'Criar primeira transação' : 'Limpar filtros'
)

const totalIncome  = computed(() => allTransactions.value.filter(t => t.category?.type === 'income').reduce((s, t) => s + Number(t.amount), 0))
const totalExpense = computed(() => allTransactions.value.filter(t => t.category?.type === 'expense').reduce((s, t) => s + Number(t.amount), 0))
const netBalance   = computed(() => totalIncome.value - totalExpense.value)

watch(filters, () => { currentPage.value = 1 }, { deep: true })

const fetchAll = async () => {
  loading.value = true
  errorMessage.value = ''
  transactionsErrorMessage.value = ''
  canRetryLoad.value = false
  try {
    const response = await transactionService.list({
      per_page: 9999,
      start_date: filters.value.dateFrom || '2000-01-01',
      end_date:   filters.value.dateTo   || new Date().toISOString().slice(0, 10),
    })
    allTransactions.value = response.data
  } catch {
    errorMessage.value = 'Não foi possível carregar as transações.'
    transactionsErrorMessage.value = errorMessage.value
    canRetryLoad.value = true
  } finally {
    loading.value = false
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
  await fetchAll()
  await fetchCategories()
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
    await fetchAll()
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
    await fetchAll()
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
  filters.value = { search: '', type: '', categoryId: '', paymentMethod: '', dateFrom: '', dateTo: '' }
}

const goPrevPage = () => {
  if (currentPage.value > 1) currentPage.value--
}

const goNextPage = () => {
  if (currentPage.value < lastPage.value) currentPage.value++
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
