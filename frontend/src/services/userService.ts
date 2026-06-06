import api from './api'

export interface UserProfile {
  id?: number
  name: string
  email: string
  phone?: string
  created_at?: string
}

export interface UpdateProfileForm {
  name: string
  email: string
}

export interface UpdatePasswordForm {
  current_password: string
  password: string
  password_confirmation: string
}

export const userService = {
  async getProfile() {
    const response = await api.get<UserProfile>('/user')
    return response.data
  },

  async updateProfile(form: UpdateProfileForm) {
    const response = await api.put<UserProfile>('/user/profile', form)
    return response.data
  },

  async updatePassword(form: UpdatePasswordForm) {
    const response = await api.put('/user/password', form)
    return response.data
  },

  async deleteAccount(password: string) {
    const response = await api.delete('/user', { data: { password } })
    return response.data
  },
}
