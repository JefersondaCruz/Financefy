<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-200"
    leave-to-class="opacity-0"
  >
    <div v-if="isOpen" class="fixed inset-0 bg-black/75 backdrop-blur-sm flex items-center justify-center z-[2000] p-4" @click="$emit('close')">
      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl w-full max-w-[480px] max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop>

        <div class="flex items-center justify-between px-6 pt-6 pb-0">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-[#4F8EF7]/12 text-[#4F8EF7] text-base">
              {{ isEditing ? '✎' : '+' }}
            </div>
            <h2 class="text-base font-bold text-white">{{ isEditing ? 'Editar Transação' : 'Nova Transação' }}</h2>
          </div>
          <button
            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/[0.06] text-[#4A6080] text-xs hover:bg-[#FF3D6B]/15 hover:text-[#FF3D6B] transition-all"
            @click="$emit('close')"
          >✕</button>
        </div>

        <form class="p-6 flex flex-col gap-4" @submit.prevent="$emit('submit', form)">

          <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Categoria</label>
            <select
              v-model="form.category_id"
              required
              class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors cursor-pointer"
            >
              <option :value="0" disabled class="bg-[#0D1526]">Selecione uma categoria...</option>
              <option v-for="c in categories" :key="c.id" :value="c.id" class="bg-[#0D1526]">{{ c.name }}</option>
            </select>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Descrição</label>
            <input
              v-model="form.description"
              type="text"
              required
              placeholder="Ex: Supermercado, Salário..."
              class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] placeholder-[#4A6080] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Valor</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-mono text-[12px] text-[#4A6080] pointer-events-none">R$</span>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  required
                  placeholder="0,00"
                  class="w-full bg-white/[0.04] border border-[#1E2D45] text-white font-mono text-[13px] placeholder-[#4A6080] pl-9 pr-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
                />
              </div>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Data</label>
              <input
                v-model="form.transaction_date"
                type="date"
                required
                class="w-full bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors [color-scheme:dark]"
              />
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Método de Pagamento</label>
            <div class="grid grid-cols-4 gap-2">
              <label
                v-for="m in paymentMethods"
                :key="m.value"
                class="flex flex-col items-center gap-1 py-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.payment_method === m.value
                  ? 'border-[#4F8EF7] bg-[#4F8EF7]/10'
                  : 'border-[#1E2D45] bg-white/[0.02] hover:border-[#4F8EF7]/50'"
              >
                <input type="radio" v-model="form.payment_method" :value="m.value" class="hidden" />
                <span class="text-lg leading-none">{{ m.icon }}</span>
                <span class="text-[10px] font-semibold text-[#4A6080] tracking-wide">{{ m.label }}</span>
              </label>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <label class="flex items-center gap-3 cursor-pointer select-none" @click="form.is_recurring = !form.is_recurring">
              <div
                class="relative w-10 h-[22px] rounded-full border transition-all duration-200 flex-shrink-0"
                :class="form.is_recurring ? 'bg-[#4F8EF7] border-[#4F8EF7]' : 'bg-white/10 border-[#1E2D45]'"
              >
                <span
                  class="absolute top-[3px] left-[3px] w-4 h-4 rounded-full bg-white transition-transform duration-200"
                  :class="form.is_recurring ? 'translate-x-[18px]' : 'translate-x-0'"
                />
              </div>
              <span class="text-[13px] text-[#4A6080]">Transação recorrente</span>
            </label>

            <input
              v-if="form.is_recurring"
              v-model="form.recurrence_type"
              type="text"
              placeholder="Ex: mensal, semanal, anual..."
              class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] placeholder-[#4A6080] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
            />
          </div>

          <div class="flex gap-3 mt-1">
            <button
              type="button"
              class="flex-1 py-3 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-[#4A6080] text-[13px] font-bold hover:bg-[#FF3D6B]/10 hover:text-[#FF3D6B] hover:border-[#FF3D6B] transition-all"
              @click="$emit('close')"
            >Cancelar</button>
            <button
              type="submit"
              class="flex-1 py-3 rounded-xl bg-[#4F8EF7] text-white text-[13px] font-bold hover:bg-[#3a7de0] transition-colors"
            >{{ isEditing ? 'Salvar alterações' : 'Adicionar transação' }}</button>
          </div>

        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue'
import type { Category, TransactionForm } from '@/types/finance'

const props = defineProps<{
  isOpen:     boolean
  isEditing:  boolean
  categories: Category[]
  initial?:   Partial<TransactionForm>
}>()

defineEmits<{
  (e: 'close'):                      void
  (e: 'submit', f: TransactionForm): void
}>()

const paymentMethods = [
  { value: 'pix',         label: 'Pix',     icon: '⚡' },
  { value: 'credit_card', label: 'Crédito', icon: '💳' },
  { value: 'money',       label: 'Dinheiro',icon: '💵' },
  { value: 'others',      label: 'Outros',  icon: '◦'  },
]

const defaultForm = (): TransactionForm => ({
  category_id: 0, description: '', amount: 0,
  transaction_date: '', payment_method: 'pix',
  is_recurring: false, recurrence_type: null,
})

const form = reactive<TransactionForm>(defaultForm())

watch(() => props.isOpen, (open) => {
  if (open && props.initial) Object.assign(form, props.initial)
  else if (!open) Object.assign(form, defaultForm())
})
</script>
