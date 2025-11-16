<template>
  <div class="register-page">
    <div class="register-card">
      <h2 class="app-title">Criar Conta</h2>

      <AppInput label="Nome" v-model="name" class="input-dark" />
      <AppInput label="Telefone" type="tel" v-model="phone" @input="onPhoneInput" class="input-dark" />
      <AppInput label="Email" type="email" v-model="email" class="input-dark" />
      <AppInput label="Senha" type="password" v-model="password" class="input-dark" />

      <p v-if="errorMessage" class="error-msg">
        {{ errorMessage }}
      </p>

      <p v-if="successMessage" class="success-msg">
        {{ successMessage }}
      </p>

      <AppButton :disabled="loading" @click="handleRegister" class="btn-green">
        {{ loading ? 'Registrando...' : 'Registrar' }}
      </AppButton>

      <p class="login-text">
        Já tem conta?
        <router-link to="/login" class="login-link">
          Entrar
        </router-link>
      </p>
    </div>
  </div>
</template>


<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { AxiosError } from 'axios'
import { useRouter } from 'vue-router'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const auth = useAuthStore()
const router = useRouter()

const name = ref('')
const phone = ref('')
const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const applyPhoneMask = (value: string) => {
  value = value.replace(/\D/g, '')

  if (value.length <= 2) return `(${value}`
  if (value.length <= 6) return `(${value.slice(0, 2)}) ${value.slice(2)}`
  if (value.length <= 10)
    return `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`
  return `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7, 11)}`
}

const onPhoneInput = () => {
  phone.value = applyPhoneMask(phone.value)
}

const handleRegister = async () => {

  if (loading.value) return;
  console.log('REGISTER DISPARADO');
  errorMessage.value = ''
  successMessage.value = ''
  loading.value = true

  try {
    const cleanPhone = phone.value.replace(/\D/g, '')

    await auth.register({
      name: name.value,
      phone: cleanPhone,
      email: email.value,
      password: password.value,
    })

    successMessage.value = 'Conta criada com sucesso! Redirecionando...'

    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (error: unknown) {
    const axiosError = error as AxiosError<{ message?: string }>
    errorMessage.value =
      axiosError.response?.data?.message ||
      'Erro ao registrar. Verifique os dados.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-page {
  background-color: #1e1e2c;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
}

.register-card {
  background: rgba(255, 255, 255, 0.05);
  padding: 32px;
  width: 100%;
  max-width: 420px;
  border-radius: 16px;
  box-shadow: 0 0 20px rgba(0, 255, 150, 0.1);
}

.app-title {
  text-align: center;
  color: #00ff9a;
  font-size: 32px;
  margin-bottom: 28px;
  font-weight: bold;
}

.input-dark :deep(input) {
  background: rgba(255, 255, 255, 0.08);
  color: white;
}

.input-dark :deep(label) {
  color: rgba(255, 255, 255, 0.6);
}

.error-msg {
  color: #ff6b6b;
  text-align: center;
  margin-top: 10px;
}

.btn-green {
  background-color: #00ff9a !important;
  color: #000 !important;
  margin-top: 16px;
  width: 100%;
  padding: 12px;
  border-radius: 12px;
}

.login-text {
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 18px;
}

.login-link {
  color: #00ff9a;
  font-weight: bold;
  text-decoration: none;
}

.success-msg {
  color: #00ff9a;
  text-align: center;
  margin-top: 10px;
}


</style>
