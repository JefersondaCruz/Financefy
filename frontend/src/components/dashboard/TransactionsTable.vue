<template>
  <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2D45] flex-wrap gap-3">
      <div class="flex items-center gap-3">
        <h4 class="text-sm font-bold text-white">{{ title }}</h4>
        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-white/[0.06] text-[#4A6080]">
          {{ totalLabel }}
        </span>
      </div>
      <div v-if="showFilters" class="flex items-center gap-2">
        <div class="relative flex items-center">
          <span class="absolute left-2.5 text-[#4A6080] text-base pointer-events-none">⌕</span>
          <input
            v-model="search"
            placeholder="Buscar..."
            class="bg-white/[0.05] border border-[#1E2D45] text-white text-[13px] placeholder-[#4A6080] rounded-xl pl-8 pr-3 py-2 outline-none w-[180px] focus:border-[#4F8EF7] transition-colors"
            @input="$emit('search', search)"
          />
        </div>
        <select
          v-model="typeFilter"
          class="bg-white/[0.05] border border-[#1E2D45] text-white text-[13px] rounded-xl px-3 py-2 outline-none cursor-pointer"
          @change="$emit('filter-type', typeFilter)"
        >
          <option value="" class="bg-[#0D1526]">Todos</option>
          <option value="income" class="bg-[#0D1526]">Receitas</option>
          <option value="expense" class="bg-[#0D1526]">Despesas</option>
        </select>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr class="bg-black/20">
            <th class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Descrição</th>
            <th class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Categoria</th>
            <th v-if="showPaymentMethod" class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Pagamento</th>
            <th class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Data</th>
            <th class="px-5 py-3 text-right text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Valor</th>
            <th class="px-5 py-3 text-right text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="errorMessage">
            <td :colspan="columnCount" class="px-5 py-12 text-center">
              <div class="flex flex-col items-center gap-3">
                <p class="text-sm font-semibold text-white">{{ errorTitle }}</p>
                <p class="max-w-[340px] text-[12px] text-[#4A6080]">{{ errorMessage }}</p>
                <button
                  v-if="errorActionLabel"
                  type="button"
                  class="rounded-xl border border-[#FF3D6B]/40 px-4 py-2 text-[12px] font-bold text-[#FF3D6B] hover:bg-[#FF3D6B]/10 transition-colors"
                  @click="$emit('error-action')"
                >
                  {{ errorActionLabel }}
                </button>
              </div>
            </td>
          </tr>
          <tr v-else-if="loading">
            <td :colspan="columnCount" class="px-5 py-12 text-center text-[#4A6080] text-sm">
              {{ loadingLabel }}
            </td>
          </tr>
          <tr v-else-if="transactions.length === 0">
            <td :colspan="columnCount" class="px-5 py-12 text-center">
              <div class="flex flex-col items-center gap-3">
                <p class="text-sm font-semibold text-white">{{ emptyTitle }}</p>
                <p class="max-w-[320px] text-[12px] text-[#4A6080]">{{ emptyDescription }}</p>
                <button
                  v-if="emptyActionLabel"
                  type="button"
                  class="rounded-xl border border-[#4F8EF7]/40 px-4 py-2 text-[12px] font-bold text-[#4F8EF7] hover:bg-[#4F8EF7]/10 transition-colors"
                  @click="$emit('empty-action')"
                >
                  {{ emptyActionLabel }}
                </button>
              </div>
            </td>
          </tr>
          <tr
            v-for="t in transactions"
            :key="t.id"
            class="border-b border-[#1E2D45] last:border-0 hover:bg-white/[0.02] transition-colors"
          >
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-2.5">
                <span
                  class="w-2 h-2 rounded-full shrink-0"
                  :class="t.category?.type === 'income'
                    ? 'bg-[#00E5A0] shadow-[0_0_6px_#00E5A0]'
                    : 'bg-[#FF3D6B] shadow-[0_0_6px_#FF3D6B]'"
                />
                <span class="text-white font-medium">{{ t.description }}</span>
                <span v-if="t.is_recurring" class="text-[10px] text-[#4F8EF7] bg-[#4F8EF7]/12 px-1.5 py-0.5 rounded font-semibold">↻ recorrente</span>
              </div>
            </td>
            <td class="px-5 py-3.5">
              <span
                class="text-[11px] font-semibold px-2.5 py-1 rounded-full"
                :class="t.category?.type === 'income'
                  ? 'bg-[#00E5A0]/10 text-[#00E5A0]'
                  : 'bg-[#FF3D6B]/10 text-[#FF3D6B]'"
              >{{ t.category?.name ?? 'Sem categoria' }}</span>
            </td>
            <td v-if="showPaymentMethod" class="px-5 py-3.5">
              <span class="flex items-center gap-1.5 text-[12px] text-[#4A6080]">
                {{ paymentMethodIcon(t.payment_method) }} {{ paymentMethodLabel(t.payment_method) }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-[#4A6080] text-[12px]">{{ formatDate(t.transaction_date) }}</td>
            <td class="px-5 py-3.5 text-right">
              <span
                class="font-mono text-[13px] font-bold"
                :class="t.category?.type === 'income' ? 'text-[#00E5A0]' : 'text-[#FF3D6B]'"
              >
                {{ t.category?.type === 'income' ? '+' : '−' }} R$ {{ formatCurrency(Number(t.amount)) }}
              </span>
            </td>
            <td class="px-5 py-3.5">
              <div class="flex gap-1.5 justify-end">
                <button
                  class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#1E2D45] bg-transparent text-[#4A6080] hover:border-[#4F8EF7] hover:text-[#4F8EF7] hover:bg-[#4F8EF7]/10 transition-all text-[13px]"
                  title="Editar"
                  @click="$emit('edit', t)"
                >✎</button>
                <button
                  class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#1E2D45] bg-transparent text-[#4A6080] hover:border-[#00E5A0] hover:text-[#00E5A0] hover:bg-[#00E5A0]/10 transition-all text-[13px]"
                  title="Duplicar"
                  @click="$emit('duplicate', t)"
                >⧉</button>
                <button
                  class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#1E2D45] bg-transparent text-[#4A6080] hover:border-[#FF3D6B] hover:text-[#FF3D6B] hover:bg-[#FF3D6B]/10 transition-all text-[13px]"
                  title="Excluir"
                  @click="$emit('delete', t)"
                >✕</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-between px-6 py-3.5 border-t border-[#1E2D45]">
      <span class="text-[12px] text-[#4A6080]">{{ paginationLabel }}</span>
      <div class="flex gap-2">
        <button
          :disabled="loading || currentPage === 1"
          class="px-4 py-1.5 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-white text-[12px] font-semibold hover:bg-[#4F8EF7] hover:border-[#4F8EF7] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          @click="$emit('prev-page')"
        >‹ Anterior</button>
        <button
          :disabled="loading || currentPage === lastPage"
          class="px-4 py-1.5 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-white text-[12px] font-semibold hover:bg-[#4F8EF7] hover:border-[#4F8EF7] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          @click="$emit('next-page')"
        >Próxima ›</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import type { Transaction } from '@/types/finance'
import {
  formatCurrency,
  formatDate,
  paymentMethodIcon,
  paymentMethodLabel,
} from '@/utils/formatters'

const props = withDefaults(defineProps<{
  title?: string
  transactions: Transaction[]
  currentPage: number
  lastPage: number
  total: number
  loading?: boolean
  loadingLabel?: string
  errorMessage?: string
  errorTitle?: string
  errorActionLabel?: string
  emptyTitle?: string
  emptyDescription?: string
  emptyActionLabel?: string
  showFilters?: boolean
  showPaymentMethod?: boolean
  paginationLabel?: string
  totalLabel?: string
}>(), {
  title: 'Transações',
  loading: false,
  loadingLabel: 'Carregando transações...',
  errorMessage: '',
  errorTitle: 'Não foi possível carregar os dados',
  errorActionLabel: '',
  emptyTitle: 'Nenhuma transação cadastrada',
  emptyDescription: 'Cadastre sua primeira transação para acompanhar seu fluxo financeiro.',
  emptyActionLabel: '',
  showFilters: true,
  showPaymentMethod: false,
  paginationLabel: '',
  totalLabel: '',
})

defineEmits<{
  (e: 'edit', t: Transaction): void
  (e: 'duplicate', t: Transaction): void
  (e: 'delete', t: Transaction): void
  (e: 'next-page'): void
  (e: 'prev-page'): void
  (e: 'search', q: string): void
  (e: 'filter-type', v: string): void
  (e: 'empty-action'): void
  (e: 'error-action'): void
}>()

const search = ref('')
const typeFilter = ref('')
const columnCount = computed(() => props.showPaymentMethod ? 6 : 5)
const totalLabel = computed(() => props.totalLabel || `${props.total} registros`)
const paginationLabel = computed(() =>
  props.paginationLabel || `Página ${props.currentPage} de ${props.lastPage}`
)
</script>
