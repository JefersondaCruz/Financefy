<template>
  <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2D45] flex-wrap gap-3">
      <div class="flex items-center gap-3">
        <h4 class="text-sm font-bold text-white">Transações</h4>
        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-white/[0.06] text-[#4A6080]">
          {{ total }} registros
        </span>
      </div>
      <div class="flex items-center gap-2">
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
            <th class="px-5 py-3 text-left text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Data</th>
            <th class="px-5 py-3 text-right text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Valor</th>
            <th class="px-5 py-3 text-right text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] border-b border-[#1E2D45]">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="transactions.length === 0">
            <td colspan="5" class="px-5 py-12 text-center text-[#4A6080] text-sm">
              Nenhuma transação encontrada
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
                  :class="t.category.type === 'income'
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
                :class="t.category.type === 'income'
                  ? 'bg-[#00E5A0]/10 text-[#00E5A0]'
                  : 'bg-[#FF3D6B]/10 text-[#FF3D6B]'"
              >{{ t.category.name }}</span>
            </td>
            <td class="px-5 py-3.5 text-[#4A6080] text-[12px]">{{ formatDate(t.transaction_date) }}</td>
            <td class="px-5 py-3.5 text-right">
              <span
                class="font-mono text-[13px] font-bold"
                :class="t.category.type === 'income' ? 'text-[#00E5A0]' : 'text-[#FF3D6B]'"
              >
                {{ t.category.type === 'income' ? '+' : '−' }} R$ {{ Math.abs(Number(t.amount)).toFixed(2) }}
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
      <span class="text-[12px] text-[#4A6080]">Página {{ currentPage }} de {{ lastPage }}</span>
      <div class="flex gap-2">
        <button
          :disabled="currentPage === 1"
          class="px-4 py-1.5 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-white text-[12px] font-semibold hover:bg-[#4F8EF7] hover:border-[#4F8EF7] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          @click="$emit('prev-page')"
        >‹ Anterior</button>
        <button
          :disabled="currentPage === lastPage"
          class="px-4 py-1.5 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-white text-[12px] font-semibold hover:bg-[#4F8EF7] hover:border-[#4F8EF7] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          @click="$emit('next-page')"
        >Próxima ›</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { Transaction } from '@/types/finance'

defineProps<{
  transactions: Transaction[]
  currentPage:  number
  lastPage:     number
  total:        number
}>()

defineEmits<{
  (e: 'edit',        t: Transaction): void
  (e: 'delete',      t: Transaction): void
  (e: 'next-page'):  void
  (e: 'prev-page'):  void
  (e: 'search',      q: string): void
  (e: 'filter-type', v: string): void
}>()

const search     = ref('')
const typeFilter = ref('')

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
</script>
