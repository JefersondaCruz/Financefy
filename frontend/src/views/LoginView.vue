<template>
  <div class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <AppCard class="w-100" style="max-width: 400px">
      <h4 class="text-center mb-3">Login</h4>

      <AppInput label="Email" type="email" v-model="email" />
      <AppInput label="Senha" type="password" v-model="password" />

      <p v-if="errorMessage" class="text-danger text-center mt-2">
        {{ errorMessage }}
      </p>

      <AppButton :loading="loading" @click="handleLogin" class="w-100 mt-3">
        Entrar
      </AppButton>

      <p class="text-center mt-3">
        Não tem conta?
        <router-link to="/register">Registre-se</router-link>
      </p>
    </AppCard>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppCard from '@/components/AppCard.vue'
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
