import { defineStore } from 'pinia'
import api from '@/services/api'
import router from '@/router'

interface User {
  id: number
  name: string
  email: string
}

interface Credentials {
  email: string
  password: string
}

interface RegisterForm extends Credentials {
  name: string
  phone: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: localStorage.getItem('token') || null,
  }),

  actions: {
    async login(credentials: Credentials) {
      const response = await api.post('/auth/login', credentials)
      const { data } = response.data
      this.token = data.token
      this.user = data.user
      localStorage.setItem('token', data.token)
    },

    async register(form: RegisterForm) {
      await api.post('/auth/register', form)
    },

    logout() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      router.push('/login')
    },
  },
})
