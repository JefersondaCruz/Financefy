<template>
  <div class="login-page">
    <div class="login-card">
      <h2 class="app-title">Financefy</h2>

      <AppInput label="Email" type="email" v-model="email" class="input-dark" />
      <AppInput label="Senha" type="password" v-model="password" class="input-dark" />

      <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

      <AppButton :loading="loading" @click="handleLogin" class="btn-green">
        Entrar
      </AppButton>

      <p class="register-text">
        Não tem conta?
        <router-link to="/register" class="register-link">
          Cadastre-se
        </router-link>
      </p>
    </div>
  </div>
</template>


<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'
import type { AxiosError } from 'axios'
import router from '@/router'

const auth = useAuthStore()
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const handleLogin = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    await auth.login({
      email: email.value,
      password: password.value,
    })
    if (auth.token) {
      router.push('/dashboard')
    }
  } catch (error: unknown) {
    const axiosError = error as AxiosError<{ message?: string }>
    errorMessage.value =
      axiosError.response?.data?.message ||
      'Falha ao fazer login. Verifique suas credenciais.'
  }finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  background-color: #1e1e2c;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
}

.login-card {
  background: rgba(255, 255, 255, 0.05);
  padding: 32px;
  width: 100%;
  max-width: 380px;
  border-radius: 16px;
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

.register-text {
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 18px;
}

.register-link {
  color: #00ff9a;
  font-weight: bold;
  text-decoration: none;
}

</style>
