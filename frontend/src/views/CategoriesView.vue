<template>
  <div class="min-h-screen bg-[#07090F] text-white flex">

    <AppSidebar :isOpen="isMenuOpen" @close="isMenuOpen = false" activeItem="Categorias" />

    <div class="flex-1 flex flex-col gap-6 px-6 py-6 md:px-8 min-w-0">

      <AppPageHeader
        title="Categorias"
        subtitle="Gerencie suas categorias de receitas e despesas"
        @open-menu="isMenuOpen = true"
      >
        <template #actions>
        <button
          class="flex items-center gap-2 bg-[#4F8EF7] hover:bg-[#3a7de0] text-white text-[13px] font-bold px-4 py-2.5 rounded-xl transition-all hover:-translate-y-px whitespace-nowrap"
          @click="openModal()"
        >
          <span class="text-lg font-light leading-none">+</span>
          Nova Categoria
        </button>
        </template>
      </AppPageHeader>

      <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="relative bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-5 overflow-hidden">
          <div class="absolute bottom-0 left-0 right-0 h-[3px] rounded-b-2xl bg-[#4F8EF7]" />
          <p class="text-[10px] font-bold tracking-[0.12em] uppercase text-[#4A6080] mb-3">Total de Categorias</p>
          <p class="font-mono text-3xl font-bold text-[#4F8EF7]">{{ categories.length }}</p>
        </div>
        <div class="relative bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-5 overflow-hidden">
          <div class="absolute bottom-0 left-0 right-0 h-[3px] rounded-b-2xl bg-[#FF3D6B]" />
          <p class="text-[10px] font-bold tracking-[0.12em] uppercase text-[#4A6080] mb-3">Despesas</p>
          <p class="font-mono text-3xl font-bold text-[#FF3D6B]">{{ expenseCategories.length }}</p>
        </div>
        <div class="relative bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-5 overflow-hidden">
          <div class="absolute bottom-0 left-0 right-0 h-[3px] rounded-b-2xl bg-[#00E5A0]" />
          <p class="text-[10px] font-bold tracking-[0.12em] uppercase text-[#4A6080] mb-3">Receitas</p>
          <p class="font-mono text-3xl font-bold text-[#00E5A0]">{{ incomeCategories.length }}</p>
        </div>
      </section>

      <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-1 bg-[#0D1526] border border-[#1E2D45] rounded-xl p-1">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            class="px-4 py-2 rounded-lg text-[12px] font-semibold transition-all"
            :class="activeTab === tab.value
              ? 'bg-[#4F8EF7] text-white'
              : 'text-[#4A6080] hover:text-white'"
            @click="activeTab = tab.value"
          >{{ tab.label }}</button>
        </div>

        <div class="relative flex items-center">
          <span class="absolute left-2.5 text-[#4A6080] text-base pointer-events-none">⌕</span>
          <input
            v-model="search"
            placeholder="Buscar categoria..."
            class="bg-[#0D1526] border border-[#1E2D45] text-white text-[13px] placeholder-[#4A6080] rounded-xl pl-8 pr-3 py-2 outline-none w-[200px] focus:border-[#4F8EF7] transition-colors"
          />
        </div>
      </div>

      <AppAlert
        v-if="errorMessage"
        :message="errorMessage"
        variant="error"
        :action-label="canRetryLoad ? 'Tentar novamente' : ''"
        @action="fetchCategories"
      />

      <AppAlert
        v-if="successMessage"
        :message="successMessage"
        variant="success"
      />

      <div v-if="loading" class="flex items-center justify-center py-20">
        <span class="text-[#4A6080] text-sm">Carregando categorias...</span>
      </div>

      <div v-else-if="filteredCategories.length === 0" class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl flex flex-col items-center justify-center py-20 gap-4">
        <span class="text-5xl">◎</span>
        <div class="text-center">
          <p class="text-sm font-semibold text-white">{{ emptyTitle }}</p>
          <p class="mt-1 max-w-[360px] text-[12px] text-[#4A6080]">{{ emptyDescription }}</p>
        </div>
        <button
          class="flex items-center gap-2 px-4 py-2 rounded-xl border border-[#1E2D45] text-[12px] font-semibold text-[#4A6080] hover:border-[#4F8EF7] hover:text-[#4F8EF7] transition-all"
          @click="emptyAction"
        >{{ emptyActionLabel }}</button>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="cat in filteredCategories"
          :key="cat.id"
          class="group bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-5 hover:border-[#2A3F5F] transition-all hover:-translate-y-0.5 duration-200 relative overflow-hidden"
        >
          <div
            class="absolute top-0 left-0 right-0 h-[2px] rounded-t-2xl"
            :class="cat.type === 'income' ? 'bg-[#00E5A0]' : 'bg-[#FF3D6B]'"
          />

          <div class="flex items-start justify-between mb-4">
            <div
              class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0"
              :class="cat.type === 'income' ? 'bg-[#00E5A0]/10' : 'bg-[#FF3D6B]/10'"
            >
              {{ cat.type === 'income' ? '📈' : '📉' }}
            </div>

            <div
                v-if="cat.user_id !== null"
                class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity"
              >
              <button
                class="w-7 h-7 flex items-center justify-center rounded-lg border border-[#1E2D45] text-[#4A6080] hover:border-[#4F8EF7] hover:text-[#4F8EF7] hover:bg-[#4F8EF7]/10 transition-all text-[12px]"
                @click="openModal(cat)"
              >✎</button>
              <button
                class="w-7 h-7 flex items-center justify-center rounded-lg border border-[#1E2D45] text-[#4A6080] hover:border-[#FF3D6B] hover:text-[#FF3D6B] hover:bg-[#FF3D6B]/10 transition-all text-[12px]"
                @click="openDeleteModal(cat)"
              >✕</button>
            </div>
          </div>

          <h3 class="text-[14px] font-bold text-white mb-1.5 truncate">{{ cat.name }}</h3>

          <span
            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full"
            :class="cat.type === 'income'
              ? 'bg-[#00E5A0]/10 text-[#00E5A0]'
              : 'bg-[#FF3D6B]/10 text-[#FF3D6B]'"
          >
            <span class="w-1.5 h-1.5 rounded-full" :class="cat.type === 'income' ? 'bg-[#00E5A0]' : 'bg-[#FF3D6B]'" />
            {{ cat.type === 'income' ? 'Receita' : 'Despesa' }}
          </span>
        </div>
      </div>

    </div>

    <CategoryModal
      :isOpen="isModalOpen"
      :isEditing="isEditing"
      :initial="editingForm"
      :saving="savingCategory"
      @close="closeModal"
      @submit="submitCategory"
    />

    <DeleteModal
      :isOpen="isDeleteModalOpen"
      :transactionName="deleteTarget?.name"
      title="Excluir categoria?"
      :loading="deletingCategory"
      :error-message="deleteErrorMessage"
      @close="closeDeleteModal"
      @confirm="confirmDelete"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { categoryService } from '@/services/categoryService'
import type { Category, CategoryForm } from '@/types/finance'

import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import AppPageHeader from '@/components/dashboard/AppPageHeader.vue'
import CategoryModal from '@/components/modals/CategoryModal.vue'
import DeleteModal from '@/components/modals/DeleteModal.vue'
import AppAlert from '@/components/AppAlert.vue'

const isMenuOpen = ref(false)
const isModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const editingForm = ref<Partial<CategoryForm>>({})
const isDeleteModalOpen = ref(false)
const deleteTarget = ref<Category | null>(null)
const categories = ref<Category[]>([])
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const canRetryLoad = ref(false)
const savingCategory = ref(false)
const deletingCategory = ref(false)
const deleteErrorMessage = ref('')
const search = ref('')
const activeTab = ref<'all' | 'income' | 'expense'>('all')

const tabs: { label: string; value: 'all' | 'income' | 'expense' }[] = [
  { label: 'Todas', value: 'all' },
  { label: 'Despesas', value: 'expense' },
  { label: 'Receitas', value: 'income' },
]

const expenseCategories = computed(() => categories.value.filter(c => c.type === 'expense'))
const incomeCategories  = computed(() => categories.value.filter(c => c.type === 'income'))

const filteredCategories = computed(() => {
  let list = categories.value
  if (activeTab.value !== 'all') list = list.filter(c => c.type === activeTab.value)
  if (search.value) list = list.filter(c => c.name.toLowerCase().includes(search.value.toLowerCase()))
  return list
})

const hasCategoryFilters = computed(() => search.value !== '' || activeTab.value !== 'all')

const emptyTitle = computed(() =>
  categories.value.length === 0
    ? 'Nenhuma categoria cadastrada'
    : 'Nenhuma categoria encontrada'
)

const emptyDescription = computed(() =>
  categories.value.length === 0
    ? 'Crie uma categoria para organizar suas receitas e despesas.'
    : 'Ajuste a busca ou veja todas as categorias para encontrar outros registros.'
)

const emptyActionLabel = computed(() =>
  hasCategoryFilters.value ? 'Limpar filtros' : '+ Criar primeira categoria'
)

const fetchCategories = async () => {
  loading.value = true
  errorMessage.value = ''
  canRetryLoad.value = false
  try {
    categories.value = await categoryService.list()
  } catch {
    errorMessage.value = 'Não foi possível carregar as categorias.'
    canRetryLoad.value = true
  } finally {
    loading.value = false
  }
}

const createCategory = async (form: CategoryForm) => {
  errorMessage.value = ''
  successMessage.value = ''
  savingCategory.value = true
  try {
    await categoryService.create(form)
    closeModal()
    await fetchCategories()
    successMessage.value = 'Categoria criada com sucesso.'
  } catch {
    errorMessage.value = 'Não foi possível criar a categoria.'
    canRetryLoad.value = false
  } finally {
    savingCategory.value = false
  }
}

const updateCategory = async (form: CategoryForm) => {
  if (!editingId.value) return
  errorMessage.value = ''
  successMessage.value = ''
  savingCategory.value = true
  try {
    await categoryService.update(editingId.value, form)
    closeModal()
    await fetchCategories()
    successMessage.value = 'Categoria atualizada com sucesso.'
  } catch {
    errorMessage.value = 'Não foi possível atualizar a categoria.'
    canRetryLoad.value = false
  } finally {
    savingCategory.value = false
  }
}

const confirmDelete = async () => {
  if (!deleteTarget.value) return
  errorMessage.value = ''
  deleteErrorMessage.value = ''
  successMessage.value = ''
  deletingCategory.value = true
  try {
    await categoryService.remove(deleteTarget.value.id)
    resetDeleteModal()
    await fetchCategories()
    successMessage.value = 'Categoria excluída com sucesso.'
  } catch {
    deleteErrorMessage.value = 'Não foi possível excluir a categoria.'
  } finally {
    deletingCategory.value = false
  }
}

const submitCategory = (form: CategoryForm) => {
  return isEditing.value ? updateCategory(form) : createCategory(form)
}

const openModal = (cat?: Category) => {
  if (cat) {
    isEditing.value = true
    editingId.value = cat.id
    editingForm.value = { name: cat.name, type: cat.type }
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

const openDeleteModal = (cat: Category) => {
  deleteTarget.value = cat
  deleteErrorMessage.value = ''
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (deletingCategory.value) return
  resetDeleteModal()
}

const resetDeleteModal = () => {
  isDeleteModalOpen.value = false
  deleteTarget.value = null
  deleteErrorMessage.value = ''
}

const emptyAction = () => {
  if (hasCategoryFilters.value) {
    search.value = ''
    activeTab.value = 'all'
  } else {
    openModal()
  }
}

onMounted(fetchCategories)
</script>
