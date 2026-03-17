<template>
  <div class="min-h-screen bg-[#07090F] text-white flex">

    <AppSidebar :isOpen="isMenuOpen" @close="isMenuOpen = false" />

    <div class="flex-1 flex flex-col">
      <!-- Topbar -->
      <header class="flex items-center gap-4 px-6 py-5">
        <button
          class="w-10 h-10 flex flex-col items-center justify-center gap-[5px] bg-[#0D1526] border border-[#1E2D45] rounded-xl hover:border-[#4F8EF7] transition-colors"
          @click="isMenuOpen = true"
        >
          <span class="block w-4 h-[1.5px] bg-white rounded" />
          <span class="block w-4 h-[1.5px] bg-white rounded" />
          <span class="block w-4 h-[1.5px] bg-white rounded" />
        </button>
        <span class="text-[13px] text-[#4A6080] font-medium">Em construção</span>
      </header>

      <!-- Content -->
      <main class="flex-1 flex flex-col items-center justify-center gap-6 px-6 text-center">
        <div class="w-24 h-24 rounded-3xl bg-[#0D1526] border border-[#1E2D45] flex items-center justify-center text-5xl">
          🚧
        </div>
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white mb-2">Em Desenvolvimento</h2>
          <p class="text-[14px] text-[#4A6080] max-w-[280px] leading-relaxed">
            Esta funcionalidade ainda está sendo construída. Em breve estará disponível.
          </p>
        </div>
        <button
          class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0D1526] border border-[#1E2D45] text-[13px] font-semibold text-[#4A6080] hover:border-[#4F8EF7] hover:text-[#4F8EF7] transition-all"
          @click="router.push('/dashboard')"
        >
          ← Voltar ao Dashboard
        </button>
      </main>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AppSidebar from '@/components/dashboard/AppSidebar.vue'

const isMenuOpen = ref(false)
const router = useRouter()
</script>
