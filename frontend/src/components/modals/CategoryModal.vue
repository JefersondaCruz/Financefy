<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-200"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black/75 backdrop-blur-sm flex items-center justify-center z-[2000] p-4"
      @click="$emit('close')"
    >
      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl w-full max-w-[420px] shadow-2xl" @click.stop>

        <!-- Header -->
        <div class="flex items-center justify-between px-6 pt-6 pb-0">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-[#4F8EF7]/12 text-[#4F8EF7] text-base">
              {{ isEditing ? '✎' : '+' }}
            </div>
            <h2 class="text-base font-bold text-white">{{ isEditing ? 'Editar Categoria' : 'Nova Categoria' }}</h2>
          </div>
          <button
            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/[0.06] text-[#4A6080] text-xs hover:bg-[#FF3D6B]/15 hover:text-[#FF3D6B] transition-all"
            @click="$emit('close')"
          >✕</button>
        </div>

        <!-- Form -->
        <form class="p-6 flex flex-col gap-4" @submit.prevent="$emit('submit', form)">

          <!-- Nome -->
          <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Nome da Categoria</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Ex: Alimentação, Salário..."
              class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] placeholder-[#4A6080] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
            />
          </div>

          <!-- Tipo -->
          <div class="flex flex-col gap-2">
            <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Tipo</label>
            <div class="grid grid-cols-2 gap-3">
              <label
                class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-all"
                :class="form.type === 'expense'
                  ? 'border-[#FF3D6B] bg-[#FF3D6B]/10'
                  : 'border-[#1E2D45] bg-white/[0.02] hover:border-[#FF3D6B]/40'"
              >
                <input type="radio" v-model="form.type" value="expense" class="hidden" />
                <span
                  class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0"
                  :class="form.type === 'expense' ? 'bg-[#FF3D6B]/15' : 'bg-white/[0.04]'"
                >📉</span>
                <div>
                  <p class="text-[13px] font-semibold" :class="form.type === 'expense' ? 'text-[#FF3D6B]' : 'text-white'">Despesa</p>
                  <p class="text-[10px] text-[#4A6080]">Saída de dinheiro</p>
                </div>
              </label>

              <label
                class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-all"
                :class="form.type === 'income'
                  ? 'border-[#00E5A0] bg-[#00E5A0]/10'
                  : 'border-[#1E2D45] bg-white/[0.02] hover:border-[#00E5A0]/40'"
              >
                <input type="radio" v-model="form.type" value="income" class="hidden" />
                <span
                  class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0"
                  :class="form.type === 'income' ? 'bg-[#00E5A0]/15' : 'bg-white/[0.04]'"
                >📈</span>
                <div>
                  <p class="text-[13px] font-semibold" :class="form.type === 'income' ? 'text-[#00E5A0]' : 'text-white'">Receita</p>
                  <p class="text-[10px] text-[#4A6080]">Entrada de dinheiro</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 mt-1">
            <button
              type="button"
              class="flex-1 py-3 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-[#4A6080] text-[13px] font-bold hover:bg-[#FF3D6B]/10 hover:text-[#FF3D6B] hover:border-[#FF3D6B] transition-all"
              @click="$emit('close')"
            >Cancelar</button>
            <button
              type="submit"
              class="flex-1 py-3 rounded-xl bg-[#4F8EF7] text-white text-[13px] font-bold hover:bg-[#3a7de0] transition-colors"
            >{{ isEditing ? 'Salvar alterações' : 'Criar categoria' }}</button>
          </div>

        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue'

interface CategoryForm {
  name: string
  type: 'income' | 'expense'
}

const props = defineProps<{
  isOpen:    boolean
  isEditing: boolean
  initial?:  Partial<CategoryForm>
}>()

defineEmits<{
  (e: 'close'):                   void
  (e: 'submit', f: CategoryForm): void
}>()

const defaultForm = (): CategoryForm => ({ name: '', type: 'expense' })
const form = reactive<CategoryForm>(defaultForm())

watch(() => props.isOpen, (open) => {
  if (open && props.initial) Object.assign(form, props.initial)
  else if (!open) Object.assign(form, defaultForm())
})
</script>
