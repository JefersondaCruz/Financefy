<template>
  <div class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <AppCard>
      <h4 class="text-center mb-3">Criar Conta</h4>

      <AppInput label="Nome" v-model="name" />
      <AppInput label="Telefone" type="tel" v-model="phone" />
      <AppInput label="Email" type="email" v-model="email" />
      <AppInput label="Senha" type="password" v-model="password" />

      <AppButton :disabled="loading" @click="handleRegister">
        {{ loading ? 'Registrando...' : 'Registrar' }}
      </AppButton>

      <p v-if="errorMessage" class="text-danger text-center mt-2">
        {{ errorMessage }}
      </p>

      <p class="text-center mt-3">
        Já tem conta?
        <router-link to="/login">Entrar</router-link>
      </p>
    </AppCard>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { AxiosError } from 'axios'

import AppCard from '@/components/AppCard.vue'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const auth = useAuthStore()

const name = ref('')
const phone = ref('')
const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const handleRegister = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    await auth.register({
      name: name.value,
      phone: phone.value,
      email: email.value,
      password: password.value,
    })
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
