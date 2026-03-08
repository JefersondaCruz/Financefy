<template>
  <div class="min-h-screen w-full flex bg-[#0f172a]">

    <!-- ── Painel Esquerdo (branding) ── -->
    <div class="hidden lg:flex w-[40%] bg-[#0f172a] border-r border-[#334155] flex-col justify-between p-12 relative overflow-hidden">
      <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1639322537228-f710d846310a?q=80&w=2832&auto=format&fit=crop')] opacity-5 bg-cover bg-center"></div>

      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-10 h-10 bg-[#3b82f6] rounded-xl flex items-center justify-center text-white text-lg font-bold">
            💬
          </div>
          <span class="text-2xl font-bold text-white tracking-tight">Financefy</span>
        </div>

        <h1 class="text-4xl font-bold text-white mb-6 leading-tight">
          Comece sua jornada
          <span class="text-[#3b82f6]">financeira hoje</span>
        </h1>

        <p class="text-[#94a3b8] text-lg mb-12 max-w-md">
          Crie sua conta gratuitamente e tenha controle total das suas finanças de forma simples e intuitiva.
        </p>

        <div class="space-y-5">
          <div v-for="feature in features" :key="feature" class="flex items-center gap-3">
            <span class="text-[#10b981] text-lg">✓</span>
            <span class="text-[#f8fafc]">{{ feature }}</span>
          </div>
        </div>
      </div>

      <div class="relative z-10 text-[#64748b] text-sm">
        © 2026 Financefy. Todos os direitos reservados.
      </div>
    </div>

    <!-- ── Painel Direito (formulário) ── -->
    <div class="flex-1 flex items-center justify-center p-8 bg-[#0b1120]">
      <div class="w-full max-w-md space-y-8">

        <div class="text-center lg:text-left">
          <h2 class="text-3xl font-bold text-white mb-2">Criar uma conta</h2>
          <p class="text-[#94a3b8]">Comece sua jornada financeira hoje</p>
        </div>

        <!-- Tab Login / Cadastro -->
        <div class="bg-[#1e293b] p-1 rounded-lg inline-flex w-full">
          <router-link
            to="/login"
            class="flex-1 py-2 text-sm font-medium rounded-md text-center transition-all text-[#94a3b8] hover:text-white"
          >
            Entrar
          </router-link>
          <router-link
            to="/register"
            class="flex-1 py-2 text-sm font-medium rounded-md text-center transition-all bg-[#3b82f6] text-white shadow-sm"
          >
            Cadastrar
          </router-link>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" class="space-y-5">
          <div>
            <label class="block text-sm text-[#94a3b8] mb-1">Nome completo</label>
            <input
              type="text"
              v-model="name"
              placeholder="João Silva"
              required
              class="w-full px-4 py-3 bg-[#1e293b] border border-[#334155] rounded-lg text-white placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors"
            />
          </div>

          <div>
            <label class="block text-sm text-[#94a3b8] mb-1">Telefone</label>
            <input
              type="tel"
              v-model="phone"
              @input="onPhoneInput"
              placeholder="(00) 00000-0000"
              class="w-full px-4 py-3 bg-[#1e293b] border border-[#334155] rounded-lg text-white placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors"
            />
          </div>

          <div>
            <label class="block text-sm text-[#94a3b8] mb-1">Email</label>
            <input
              type="email"
              v-model="email"
              placeholder="voce@exemplo.com"
              required
              class="w-full px-4 py-3 bg-[#1e293b] border border-[#334155] rounded-lg text-white placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors"
            />
          </div>

          <div>
            <label class="block text-sm text-[#94a3b8] mb-1">Senha</label>
            <input
              type="password"
              v-model="password"
              placeholder="••••••••"
              required
              class="w-full px-4 py-3 bg-[#1e293b] border border-[#334155] rounded-lg text-white placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors"
            />
          </div>

          <p v-if="errorMessage" class="text-[#f43f5e] text-sm text-center">{{ errorMessage }}</p>
          <p v-if="successMessage" class="text-[#10b981] text-sm text-center">{{ successMessage }}</p>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 bg-[#3b82f6] hover:bg-[#2563eb] disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
          >
            <span>{{ loading ? 'Criando conta...' : 'Criar Conta' }}</span>
            <span v-if="!loading">→</span>
          </button>
        </form>

        <p class="text-center text-sm text-[#64748b]">
          Já tem uma conta?
          <router-link to="/login" class="text-[#3b82f6] hover:text-[#60a5fa] font-medium ml-1">
            Entrar
          </router-link>
        </p>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { AxiosError } from 'axios'
import { useRouter } from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()

const name           = ref('')
const phone          = ref('')
const email          = ref('')
const password       = ref('')
const loading        = ref(false)
const errorMessage   = ref('')
const successMessage = ref('')

const features = [
  'Processamento de linguagem natural',
  'Análise financeira em tempo real',
  'Categorização automática inteligente',
]

const applyPhoneMask = (value: string) => {
  value = value.replace(/\D/g, '')
  if (value.length <= 2)  return `(${value}`
  if (value.length <= 6)  return `(${value.slice(0, 2)}) ${value.slice(2)}`
  if (value.length <= 10) return `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`
  return `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7, 11)}`
}

const onPhoneInput = () => { phone.value = applyPhoneMask(phone.value) }

const handleRegister = async () => {
  if (loading.value) return
  errorMessage.value   = ''
  successMessage.value = ''
  loading.value        = true
  try {
    const cleanPhone = phone.value.replace(/\D/g, '')
    await auth.register({ name: name.value, phone: cleanPhone, email: email.value, password: password.value })
    successMessage.value = 'Conta criada com sucesso! Redirecionando...'
    setTimeout(() => router.push('/login'), 1500)
  } catch (error: unknown) {
    const axiosError = error as AxiosError<{ message?: string }>
    errorMessage.value = axiosError.response?.data?.message || 'Erro ao registrar. Verifique os dados.'
  } finally {
    loading.value = false
  }
}
</script>
