import { defineStore } from 'pinia'
import { authService } from '@/services/authService'
import type { AuthUser, Credentials, RegisterForm } from '@/services/authService'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as AuthUser | null,
    token: localStorage.getItem('token') || null,
  }),

  actions: {
    async login(credentials: Credentials) {
      const { data } = await authService.login(credentials)
      this.token = data.token
      this.user = data.user
      localStorage.setItem('token', data.token)
    },

    async register(form: RegisterForm) {
      await authService.register(form)
    },

    logout() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      router.push('/login')
    },

    setUser(user: AuthUser) {
      this.user = user
    },
  },
})
