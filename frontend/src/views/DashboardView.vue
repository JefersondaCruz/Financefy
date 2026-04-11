<template>
  <div class="min-h-screen bg-[#07090F] text-white flex">

    <AppSidebar :isOpen="isMenuOpen" activeItem="Dashboard" @close="isMenuOpen = false" />

    <!-- Main -->
    <div class="flex-1 flex flex-col gap-6 px-6 py-6 md:px-8 min-w-0">

      <!-- Top bar -->
      <header class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
          <!-- Hamburger -->
          <button
            class="w-10 h-10 flex flex-col items-center justify-center gap-[5px] bg-[#0D1526] border border-[#1E2D45] rounded-xl hover:border-[#4F8EF7] transition-colors"
            @click="isMenuOpen = true"
          >
            <span class="block w-4 h-[1.5px] bg-white rounded" />
            <span class="block w-4 h-[1.5px] bg-white rounded" />
            <span class="block w-4 h-[1.5px] bg-white rounded" />
          </button>
          <div>
            <h1 class="text-xl font-extrabold tracking-tight text-white leading-tight">Dashboard</h1>
            <p class="text-[12px] text-[#4A6080] mt-0.5">{{ greeting }}, bem-vindo de volta.</p>
          </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
          <DateRangeFilter @change="onDateChange" />
          <button
            class="flex items-center gap-2 bg-[#4F8EF7] hover:bg-[#3a7de0] text-white text-[13px] font-bold px-4 py-2.5 rounded-xl transition-colors hover:-translate-y-px duration-150 whitespace-nowrap"
            @click="openModal"
          >
            <span class="text-lg font-light leading-none">+</span>
            Nova Transação
          </button>
        </div>
      </header>

      <!-- KPIs -->
      <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <KpiCard label="Despesas Totais" icon="📉" :value="totalExpenses" :trend="12"  variant="expense" />
        <KpiCard label="Receitas Totais" icon="📈" :value="totalIncome"   :trend="8"   variant="income"  />
        <KpiCard label="Saldo Líquido"   icon="💰" :value="netBalance"    :trend="netBalance >= 0 ? 5 : -5" variant="balance" />
      </section>

      <!-- Charts -->
      <section class="grid grid-cols-1 lg:grid-cols-[1fr_1.4fr] gap-5">
        <CategoryChart :transactions="transactions" />
        <TrendChart    :transactions="transactions" />
      </section>

      <!-- Table -->
      <TransactionsTable
        :transactions="filteredTransactions"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="total"
        @edit="openEditModal"
        @delete="openDeleteModal"
        @next-page="nextPage"
        @prev-page="prevPage"
        @search="q => (searchQuery = q)"
        @filter-type="v => (typeFilter = v)"
      />

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
      @close="closeDeleteModal"
      @confirm="confirmDelete"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { dashboardService } from '@/services/dashboardService'

import type { Transaction, Category, TransactionForm, DateFilter } from '@/types/finance'

import AppSidebar        from '@/components/dashboard/AppSidebar.vue'
import DateRangeFilter   from '@/components/dashboard/DateRangeFilter.vue'
import KpiCard           from '@/components/dashboard/KpiCard.vue'
import CategoryChart     from '@/components/dashboard/CategoryChart.vue'
import TrendChart        from '@/components/dashboard/TrendChart.vue'
import TransactionsTable from '@/components/dashboard/TransactionsTable.vue'
import TransactionModal  from '@/components/modals/TransactionModal.vue'
import DeleteModal       from '@/components/modals/DeleteModal.vue'

// UI
const isMenuOpen        = ref(false)
const isModalOpen       = ref(false)
const isEditing         = ref(false)
const editingId         = ref<number | null>(null)
const editingForm       = ref<Partial<TransactionForm>>({})
const isDeleteModalOpen = ref(false)
const deleteTarget      = ref<Transaction | null>(null)

// Data
const transactions = ref<Transaction[]>([])
const categories   = ref<Category[]>([])
const currentPage  = ref(1)
const lastPage     = ref(1)
const total        = ref(0)
const perPage      = ref(10)

// Filters
const searchQuery = ref('')
const typeFilter  = ref('')
const now = new Date()
const dateFilter = ref<DateFilter>({
  start_date: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`,
  end_date:   `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()}`,
})
// Computed
const totalExpenses = computed(() =>
  transactions.value.filter(t => t.category.type === 'expense').reduce((s, t) => s + Number(t.amount), 0)
)
const totalIncome = computed(() =>
  transactions.value.filter(t => t.category.type === 'income').reduce((s, t) => s + Number(t.amount), 0)
)
const netBalance = computed(() => totalIncome.value - totalExpenses.value)

const filteredTransactions = computed(() => {
  let list = transactions.value
  if (searchQuery.value)
    list = list.filter(t => t.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  if (typeFilter.value)
    list = list.filter(t => t.category.type === typeFilter.value)
  return list
})

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Bom dia'; if (h < 18) return 'Boa tarde'; return 'Boa noite'
})

// API
const fetchTransactions = async (page = 1) => {
  try {
    const { data } = await dashboardService.getTransactions({
      page,
      per_page: perPage.value,
      start_date: dateFilter.value.start_date,
      end_date:   dateFilter.value.end_date,
    })
    transactions.value = data.data
    currentPage.value  = data.current_page
    lastPage.value     = data.last_page
    total.value        = data.total
  } catch (e) { console.error(e) }
}

const fetchCategories   = () => dashboardService.getCategories().then(r => categories.value = r.data).catch(console.error)


const createTransaction = async (form: TransactionForm) => { await dashboardService.createTransaction(form);                        closeModal(); fetchTransactions() }


const updateTransaction = async (form: TransactionForm) => { await dashboardService.updateTransaction(editingId.value!, form);      closeModal(); fetchTransactions(currentPage.value) }


const confirmDelete     = async ()                       => { await dashboardService.deleteTransaction(deleteTarget.value!.id);      closeDeleteModal(); fetchTransactions(currentPage.value) }


// Handlers
const submitTransaction = (form: TransactionForm) => isEditing.value ? updateTransaction(form) : createTransaction(form)

const openModal = () => { isEditing.value = false; editingForm.value = {}; isModalOpen.value = true }

const openEditModal = (t: Transaction) => {
  isEditing.value = true; editingId.value = t.id
  editingForm.value = {
    category_id: t.category_id, description: t.description,
    amount: t.amount, transaction_date: t.transaction_date,
    payment_method: t.payment_method, is_recurring: t.is_recurring ?? false,
    recurrence_type: t.recurrence_type ?? null,
  }
  isModalOpen.value = true
}

const closeModal       = () => { isModalOpen.value = false; isEditing.value = false; editingId.value = null; editingForm.value = {} }
const openDeleteModal  = (t: Transaction) => { deleteTarget.value = t; isDeleteModalOpen.value = true }
const closeDeleteModal = () => { isDeleteModalOpen.value = false; deleteTarget.value = null }
const onDateChange     = (df: DateFilter) => { dateFilter.value = df; fetchTransactions(1) }
const nextPage         = () => fetchTransactions(currentPage.value + 1)
const prevPage         = () => fetchTransactions(currentPage.value - 1)

onMounted(async () => { await fetchTransactions(); await fetchCategories() })
</script>
