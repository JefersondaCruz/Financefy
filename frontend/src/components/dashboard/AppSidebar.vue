<template>
  <!-- Overlay -->
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-200"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[999]"
      @click="$emit('close')"
    />
  </Transition>

  <!-- Drawer -->
  <aside
    class="fixed top-0 h-full w-[240px] bg-[#0D1526] border-r border-[#1E2D45] flex flex-col z-[1000] transition-transform duration-250"
    :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
  >
    <!-- Brand -->
    <div class="flex items-center gap-3 px-6 py-7 border-b border-[#1E2D45]">
      <span class="text-[#4F8EF7] text-2xl leading-none">◈</span>
      <span class="text-white font-extrabold text-lg tracking-[0.06em] uppercase">Finflow</span>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-4 flex flex-col gap-0.5">
      <button
        v-for="item in navItems"
        :key="item.label"
        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-medium transition-all duration-150 text-left"
        :class="item.active
          ? 'bg-[#4F8EF7]/12 text-[#4F8EF7]'
          : 'text-[#4A6080] hover:bg-white/5 hover:text-white'"
        @click="item.action(); $emit('close')"
      >
        <span class="text-base w-5 text-center">{{ item.icon }}</span>
        <span class="flex-1">{{ item.label }}</span>
        <span v-if="item.active" class="w-1.5 h-1.5 rounded-full bg-[#4F8EF7]" />
      </button>
    </nav>

    <!-- Logout -->
    <div class="px-3 pb-6 pt-3 border-t border-[#1E2D45]">
      <button
        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm text-[#FF3D6B] hover:bg-[#FF3D6B]/10 transition-colors duration-150"
        @click="handleLogout"
      >
        <span>↩</span>
        <span>Sair da conta</span>
      </button>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

const props = defineProps<{ isOpen: boolean; activeItem?: string }>()
defineEmits<{ (e: 'close'): void }>()

const auth = useAuthStore()

const navItemDefs = [
  { icon: '▦', label: 'Dashboard',    route: '/dashboard'    },
  { icon: '↕', label: 'Transações',   route: '/transactions' },
  { icon: '◎', label: 'Categorias',   route: '/categories'   },
  { icon: '✦', label: 'IA Financeira',route: '/ai'           },
  { icon: '⊙', label: 'Perfil',       route: '/profile'      },
]

const navItems = computed(() =>
  navItemDefs.map(item => ({
    ...item,
    active: (props.activeItem ?? 'Dashboard') === item.label,
    action: () => router.push(item.route),
  }))
)

const handleLogout = () => auth.logout()
</script>
