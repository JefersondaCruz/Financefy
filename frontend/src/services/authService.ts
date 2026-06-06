import api from './api'

export interface AuthUser {
  id: number
  name: string
  email: string
  phone?: string
}

export interface Credentials {
  email: string
  password: string
}

export interface RegisterForm extends Credentials {
  name: string
  phone: string
}

interface AuthPayload {
  data: {
    token: string
    user: AuthUser
  }
}

export const authService = {
  async login(credentials: Credentials) {
    const response = await api.post<AuthPayload>('/auth/login', credentials)
    return response.data
  },

  async register(form: RegisterForm) {
    const response = await api.post<AuthPayload>('/auth/register', form)
    return response.data
  },
}
