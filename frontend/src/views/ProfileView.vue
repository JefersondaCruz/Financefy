<template>
  <div class="min-h-screen bg-[#07090F] text-white flex">

    <AppSidebar :isOpen="isMenuOpen" activeItem="Perfil" @close="isMenuOpen = false" />

    <div class="flex-1 flex flex-col gap-6 px-6 py-6 md:px-8 min-w-0 max-w-[800px]">

      <!-- Topbar -->
      <header class="flex items-center gap-4">
        <button class="w-10 h-10 flex flex-col items-center justify-center gap-[5px] bg-[#0D1526] border border-[#1E2D45] rounded-xl hover:border-[#4F8EF7] transition-colors" @click="isMenuOpen = true">
          <span class="block w-4 h-[1.5px] bg-white rounded" />
          <span class="block w-4 h-[1.5px] bg-white rounded" />
          <span class="block w-4 h-[1.5px] bg-white rounded" />
        </button>
        <div>
          <h1 class="text-xl font-extrabold tracking-tight text-white leading-tight">Perfil</h1>
          <p class="text-[12px] text-[#4A6080] mt-0.5">Gerencie suas informações pessoais</p>
        </div>
      </header>

      <!-- Avatar + name section -->
      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-6 flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-[#4F8EF7]/15 border border-[#4F8EF7]/30 flex items-center justify-center text-2xl font-bold text-[#4F8EF7] shrink-0">
          {{ initials }}
        </div>
        <div>
          <h2 class="text-lg font-bold text-white">{{ profileForm.name || 'Seu nome' }}</h2>
          <p class="text-[13px] text-[#4A6080]">{{ profileForm.email }}</p>
          <p class="text-[11px] text-[#4A6080] mt-1">Membro desde {{ memberSince }}</p>
        </div>
      </div>

      <!-- Profile form -->
      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1E2D45]">
          <h3 class="text-sm font-bold text-white">Informações Pessoais</h3>
        </div>
        <form class="p-6 flex flex-col gap-4" @submit.prevent="saveProfile">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Nome completo</label>
              <input
                v-model="profileForm.name"
                type="text"
                required
                class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
              />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">E-mail</label>
              <input
                v-model="profileForm.email"
                type="email"
                required
                class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
              />
            </div>
          </div>

          <!-- Feedback message -->
          <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
            <p v-if="profileMsg" class="text-[12px] font-semibold" :class="profileMsg.type === 'success' ? 'text-[#00E5A0]' : 'text-[#FF3D6B]'">
              {{ profileMsg.text }}
            </p>
          </Transition>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="savingProfile"
              class="px-6 py-2.5 rounded-xl bg-[#4F8EF7] text-white text-[13px] font-bold hover:bg-[#3a7de0] disabled:opacity-50 transition-all"
            >{{ savingProfile ? 'Salvando...' : 'Salvar alterações' }}</button>
          </div>
        </form>
      </div>

      <!-- Password form -->
      <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1E2D45]">
          <h3 class="text-sm font-bold text-white">Alterar Senha</h3>
        </div>
        <form class="p-6 flex flex-col gap-4" @submit.prevent="changePassword">
          <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Senha atual</label>
            <input
              v-model="passwordForm.current_password"
              type="password"
              required
              class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Nova senha</label>
              <input
                v-model="passwordForm.password"
                type="password"
                required
                minlength="8"
                class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
              />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080]">Confirmar nova senha</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] px-3 py-2.5 rounded-xl outline-none focus:border-[#4F8EF7] transition-colors"
              />
            </div>
          </div>

          <!-- Password strength indicator -->
          <div v-if="passwordForm.password" class="flex flex-col gap-1.5">
            <div class="flex gap-1.5">
              <div v-for="i in 4" :key="i" class="flex-1 h-1 rounded-full transition-all duration-300"
                :class="passwordStrength >= i ? strengthColor : 'bg-[#1E2D45]'" />
            </div>
            <p class="text-[11px]" :class="strengthColor.replace('bg-', 'text-')">{{ strengthLabel }}</p>
          </div>

          <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
            <p v-if="passwordMsg" class="text-[12px] font-semibold" :class="passwordMsg.type === 'success' ? 'text-[#00E5A0]' : 'text-[#FF3D6B]'">
              {{ passwordMsg.text }}
            </p>
          </Transition>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="savingPassword || passwordForm.password !== passwordForm.password_confirmation"
              class="px-6 py-2.5 rounded-xl bg-[#4F8EF7] text-white text-[13px] font-bold hover:bg-[#3a7de0] disabled:opacity-50 transition-all"
            >{{ savingPassword ? 'Alterando...' : 'Alterar senha' }}</button>
          </div>
        </form>
      </div>

      <!-- Danger zone -->
      <div class="bg-[#0D1526] border border-[#FF3D6B]/20 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-[#FF3D6B]/20">
          <h3 class="text-sm font-bold text-[#FF3D6B]">Zona de Perigo</h3>
        </div>
        <div class="p-6 flex items-center justify-between flex-wrap gap-4">
          <div>
            <p class="text-[13px] font-semibold text-white">Excluir minha conta</p>
            <p class="text-[12px] text-[#4A6080] mt-0.5">Esta ação é permanente e não pode ser desfeita.</p>
          </div>
          <button
            class="px-5 py-2.5 rounded-xl border border-[#FF3D6B]/40 text-[#FF3D6B] text-[13px] font-bold hover:bg-[#FF3D6B]/10 transition-all"
            @click="showDeleteAccount = true"
          >Excluir conta</button>
        </div>
      </div>

      <!-- Confirm delete account modal -->
      <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
        <div v-if="showDeleteAccount" class="fixed inset-0 bg-black/75 backdrop-blur-sm flex items-center justify-center z-[2000] p-4" @click="showDeleteAccount = false">
          <div class="bg-[#0D1526] border border-[#1E2D45] rounded-2xl p-8 w-full max-w-[380px] text-center shadow-2xl" @click.stop>
            <div class="w-14 h-14 rounded-full bg-[#FF3D6B]/10 border border-[#FF3D6B]/20 flex items-center justify-center text-2xl mx-auto mb-5">⚠</div>
            <h3 class="text-lg font-bold text-white mb-2">Excluir sua conta?</h3>
            <p class="text-[13px] text-[#4A6080] leading-relaxed mb-6">Todos os seus dados serão removidos permanentemente. Esta ação não pode ser desfeita.</p>
            <div class="flex flex-col gap-2 mb-4">
              <label class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#4A6080] text-left">Digite sua senha para confirmar</label>
              <input v-model="deleteAccountPassword" type="password" placeholder="Sua senha"
                class="bg-white/[0.04] border border-[#1E2D45] text-white text-[13px] placeholder-[#4A6080] px-3 py-2.5 rounded-xl outline-none focus:border-[#FF3D6B] transition-colors" />
            </div>
            <div class="flex gap-3">
              <button class="flex-1 py-3 rounded-xl border border-[#1E2D45] bg-white/[0.04] text-[#4A6080] text-[13px] font-bold hover:bg-white/10 hover:text-white transition-all" @click="showDeleteAccount = false">Cancelar</button>
              <button
                :disabled="!deleteAccountPassword"
                class="flex-1 py-3 rounded-xl bg-[#FF3D6B] text-white text-[13px] font-bold hover:bg-[#e02050] disabled:opacity-50 transition-colors"
                @click="deleteAccount"
              >Excluir</button>
            </div>
          </div>
        </div>
      </Transition>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import AppSidebar from '@/components/dashboard/AppSidebar.vue'

const auth = useAuthStore()

const isMenuOpen          = ref(false)
const savingProfile       = ref(false)
const savingPassword      = ref(false)
const showDeleteAccount   = ref(false)
const deleteAccountPassword = ref('')

const profileForm = ref({ name: '', email: '', created_at: '' })
const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' })

interface Msg { type: 'success' | 'error'; text: string }
const profileMsg  = ref<Msg | null>(null)
const passwordMsg = ref<Msg | null>(null)

// ── Computed ───────────────────────────────────────────────────────────────
const initials = computed(() =>
  profileForm.value.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() || 'U'
)

const memberSince = computed(() =>
  profileForm.value.created_at
    ? new Date(profileForm.value.created_at).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
    : ''
)

const passwordStrength = computed(() => {
  const p = passwordForm.value.password
  let score = 0
  if (p.length >= 8)  score++
  if (/[A-Z]/.test(p)) score++
  if (/[0-9]/.test(p)) score++
  if (/[^A-Za-z0-9]/.test(p)) score++
  return score
})

const strengthColor = computed(() => {
  const map = ['bg-[#FF3D6B]', 'bg-[#F59E0B]', 'bg-[#4F8EF7]', 'bg-[#00E5A0]']
  return map[passwordStrength.value - 1] ?? 'bg-[#1E2D45]'
})

const strengthLabel = computed(() => {
  return ['', 'Fraca', 'Razoável', 'Boa', 'Forte'][passwordStrength.value] ?? ''
})

// ── API ────────────────────────────────────────────────────────────────────
const fetchProfile = async () => {
  try { const { data } = await api.get('/user'); profileForm.value = data }
  catch (e) { console.error(e) }
}

const saveProfile = async () => {
  savingProfile.value = true; profileMsg.value = null
  try {
    await api.put('/user/profile', { name: profileForm.value.name, email: profileForm.value.email })
    profileMsg.value = { type: 'success', text: '✓ Perfil atualizado com sucesso!' }
    setTimeout(() => { profileMsg.value = null }, 3000)
  } catch {
    profileMsg.value = { type: 'error', text: 'Erro ao atualizar perfil. Tente novamente.' }
  } finally { savingProfile.value = false }
}

const changePassword = async () => {
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    passwordMsg.value = { type: 'error', text: 'As senhas não coincidem.' }
    return
  }
  savingPassword.value = true; passwordMsg.value = null
  try {
    await api.put('/user/password', passwordForm.value)
    passwordMsg.value = { type: 'success', text: '✓ Senha alterada com sucesso!' }
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    setTimeout(() => { passwordMsg.value = null }, 3000)
  } catch {
    passwordMsg.value = { type: 'error', text: 'Senha atual incorreta ou erro no servidor.' }
  } finally { savingPassword.value = false }
}

const deleteAccount = async () => {
  try {
    await api.delete('/user', { data: { password: deleteAccountPassword.value } })
    auth.logout()
  } catch {
    showDeleteAccount.value = false
    deleteAccountPassword.value = ''
  }
}

onMounted(fetchProfile)
</script>
