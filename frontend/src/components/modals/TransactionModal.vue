<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-200"
    leave-to-class="opacity-0"
  >
    <div v-if="isOpen" class="fixed inset-0 bg-black/75 backdrop-blur-sm flex items-center justify-center z-[2000] p-4" @click="onBackdropClick">
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
            :disabled="saving"
            @click="close"
          >✕</button>
        </div>

        <div class="p-6 flex flex-col gap-4">

          <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Categoria</label>

            <div class="relative" ref="categoryRef">
              <button
                type="button"
                @click="toggleCategoryDropdown"
                class="w-full flex items-center justify-between bg-white/[0.04] px-3 py-2.5 rounded-xl text-[13px] transition-colors outline-none"
                :class="errors.category_id
                  ? 'border border-[#FF3D6B]'
                  : categoryOpen
                    ? 'border border-[#4F8EF7]'
                    : 'border border-[#1E2D45] hover:border-[#4F8EF7]/50'"
              >
                <span :class="form.category_id === 0 ? 'text-[#4A6080]' : 'text-white'">
                  {{ selectedCategoryLabel }}
                </span>
                <span
                  class="text-[#4A6080] text-[10px] transition-transform duration-200"
                  :class="categoryOpen ? 'rotate-180' : ''"
                >▼</span>
              </button>

              <Transition
                enter-active-class="transition-all duration-150 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                leave-active-class="transition-all duration-100 ease-in"
                leave-to-class="opacity-0 -translate-y-1"
              >
                <div
                  v-if="categoryOpen"
                  class="absolute z-50 mt-1.5 w-full bg-[#0D1526] border border-[#1E2D45] rounded-xl shadow-2xl overflow-hidden"
                >
                  <div class="max-h-44 overflow-y-auto">
                    <button
                      v-for="c in categories"
                      :key="c.id"
                      type="button"
                      @click="selectCategory(c.id)"
                      class="w-full text-left px-4 py-2.5 text-[13px] transition-colors"
                      :class="form.category_id === c.id
                        ? 'bg-[#4F8EF7]/15 text-[#4F8EF7]'
                        : 'text-[#8BA3C0] hover:bg-white/[0.05] hover:text-white'"
                    >
                      {{ c.name }}
                    </button>
                  </div>
                </div>
              </Transition>
            </div>

            <p v-if="errors.category_id" class="text-[11px] text-[#FF3D6B] flex items-center gap-1">
              <span>⚠</span> {{ errors.category_id }}
            </p>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Descrição</label>
            <input
              v-model="form.description"
              type="text"
              placeholder="Ex: Supermercado, Salário..."
              @input="clearError('description')"
              class="bg-white/[0.04] text-white text-[13px] placeholder-[#4A6080] px-3 py-2.5 rounded-xl outline-none transition-colors"
              :class="errors.description
                ? 'border border-[#FF3D6B]'
                : 'border border-[#1E2D45] focus:border-[#4F8EF7]'"
            />
            <p v-if="errors.description" class="text-[11px] text-[#FF3D6B] flex items-center gap-1">
              <span>⚠</span> {{ errors.description }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-4">

            <div class="flex flex-col gap-1.5">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Valor</label>
              <div
                class="flex items-stretch rounded-xl overflow-hidden transition-colors"
                :class="errors.amount
                  ? 'border border-[#FF3D6B]'
                  : 'border border-[#1E2D45] focus-within:border-[#4F8EF7]'"
              >
                <span class="pl-3 bg-white/[0.04] font-mono text-[12px] text-[#4A6080] pointer-events-none flex items-center flex-shrink-0">R$</span>
                <input
                  ref="amountInput"
                  type="text"
                  inputmode="numeric"
                  placeholder="0,00"
                  :value="displayAmount"
                  @input="onAmountInput"
                  @keydown="onAmountKeydown"
                  class="flex-1 min-w-0 bg-white/[0.04] text-white font-mono text-[13px] placeholder-[#4A6080] px-2 py-2.5 outline-none"
                />
                <div class="flex flex-col border-l border-[#1E2D45] bg-white/[0.04] flex-shrink-0">
                  <button type="button" tabindex="-1" @click="stepAmount(+1)" @mousedown.prevent
                    class="w-7 flex-1 flex items-center justify-center text-[#4A6080] hover:text-[#4F8EF7] hover:bg-[#4F8EF7]/10 transition-all text-[10px] border-b border-[#1E2D45]"
                  >▲</button>
                  <button type="button" tabindex="-1" @click="stepAmount(-1)" @mousedown.prevent
                    class="w-7 flex-1 flex items-center justify-center text-[#4A6080] hover:text-[#FF3D6B] hover:bg-[#FF3D6B]/10 transition-all text-[10px]"
                  >▼</button>
                </div>
              </div>
              <p v-if="errors.amount" class="text-[11px] text-[#FF3D6B] flex items-center gap-1">
                <span>⚠</span> {{ errors.amount }}
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Data</label>
              <input
                v-model="form.transaction_date"
                type="date"
                @change="clearError('transaction_date')"
                class="w-full bg-white/[0.04] text-white text-[13px] px-3 py-2.5 rounded-xl outline-none transition-colors [color-scheme:dark]"
                :class="errors.transaction_date
                  ? 'border border-[#FF3D6B]'
                  : 'border border-[#1E2D45] focus:border-[#4F8EF7]'"
              />
              <p v-if="errors.transaction_date" class="text-[11px] text-[#FF3D6B] flex items-center gap-1">
                <span>⚠</span> {{ errors.transaction_date }}
              </p>
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
              :disabled="saving"
              class="flex-1 py-3 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-[#4A6080] text-[13px] font-bold hover:bg-[#FF3D6B]/10 hover:text-[#FF3D6B] hover:border-[#FF3D6B] transition-all"
              @click="close"
            >Cancelar</button>
            <button
              type="button"
              :disabled="saving"
              @click="handleSubmit"
              class="flex-1 py-3 rounded-xl bg-[#4F8EF7] text-white text-[13px] font-bold hover:bg-[#3a7de0] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >{{ saving ? 'Salvando...' : isEditing ? 'Salvar alterações' : 'Adicionar transação' }}</button>
          </div>

        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { reactive, ref, computed, watch, onMounted, onUnmounted } from 'vue'
import type { Category, TransactionForm } from '@/types/finance'
import { paymentMethodOptions } from '@/utils/formatters'

const props = defineProps<{
  isOpen: boolean
  isEditing: boolean
  categories: Category[]
  initial?: Partial<TransactionForm>
  saving?: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', f: TransactionForm): void
}>()

const paymentMethods = paymentMethodOptions

const categoryOpen = ref(false)
const categoryRef  = ref<HTMLElement | null>(null)

const selectedCategoryLabel = computed(() =>
  form.category_id === 0
    ? 'Selecione uma categoria...'
    : props.categories.find(c => c.id === form.category_id)?.name ?? 'Selecione uma categoria...'
)

function toggleCategoryDropdown() {
  categoryOpen.value = !categoryOpen.value
}

function selectCategory(id: number) {
  form.category_id = id
  categoryOpen.value = false
  clearError('category_id')
}

function onClickOutside(e: MouseEvent) {
  if (categoryRef.value && !categoryRef.value.contains(e.target as Node)) {
    categoryOpen.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside))

function todayISO(): string {
  const d = new Date()
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

function centsToBRL(cents: number): string {
  if (!cents) return ''
  return (cents / 100).toFixed(2).replace('.', ',')
}

function parseCents(raw: string): number {
  const digits = raw.replace(/\D/g, '')
  return parseInt(digits || '0', 10)
}

const amountCents = ref(0)
const amountInput = ref<HTMLInputElement | null>(null)
const displayAmount = computed(() => centsToBRL(amountCents.value))

function onAmountInput(e: Event) {
  const el = e.target as HTMLInputElement
  amountCents.value = parseCents(el.value)
  clearError('amount')
  requestAnimationFrame(() => {
    if (amountInput.value) {
      const len = amountInput.value.value.length
      amountInput.value.setSelectionRange(len, len)
    }
  })
}

function onAmountKeydown(e: KeyboardEvent) {
  const allowed = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End']
  if (allowed.includes(e.key)) return
  if (!/^\d$/.test(e.key)) e.preventDefault()
}

function stepAmount(direction: 1 | -1) {
  amountCents.value = Math.max(0, amountCents.value + direction * 100)
  clearError('amount')
  amountInput.value?.focus()
}

type ErrorMap = Partial<Record<keyof TransactionForm, string>>
const errors = reactive<ErrorMap>({})

function clearError(field: keyof TransactionForm) {
  delete errors[field]
}

function validate(): boolean {
  ;(Object.keys(errors) as Array<keyof TransactionForm>).forEach(k => delete errors[k])

  if (!form.category_id)         errors.category_id      = 'Selecione uma categoria.'
  if (!form.description.trim())  errors.description      = 'Informe uma descrição.'
  if (amountCents.value <= 0)    errors.amount            = 'Informe um valor maior que zero.'
  if (!form.transaction_date)    errors.transaction_date  = 'Selecione uma data.'

  return Object.keys(errors).length === 0
}

const defaultForm = (): TransactionForm => ({
  category_id: 0,
  description: '',
  amount: 0,
  transaction_date: todayISO(),
  payment_method: 'pix',
  is_recurring: false,
  recurrence_type: null,
})

const form = reactive<TransactionForm>(defaultForm())

watch(() => props.isOpen, (open) => {
  if (open && props.initial) {
    Object.assign(form, { ...defaultForm(), ...props.initial })
    amountCents.value = Math.round((props.initial.amount ?? 0) * 100)
  } else if (!open) {
    Object.assign(form, defaultForm())
    amountCents.value  = 0
    categoryOpen.value = false
    ;(Object.keys(errors) as Array<keyof TransactionForm>).forEach(k => delete errors[k])
  }
})

function handleSubmit() {
  if (props.saving) return
  if (!validate()) return
  emit('submit', { ...form, amount: amountCents.value / 100 })
}

function close() {
  if (!props.saving) emit('close')
}

function onBackdropClick() {
  if (props.saving) return
  categoryOpen.value = false
  emit('close')
}
</script>
