import api from './api'
import type { Category, CategoryForm } from '@/types/finance'

export const categoryService = {
  async list() {
    const response = await api.get<Category[]>('/categories')
    return response.data
  },

  async create(form: CategoryForm) {
    const response = await api.post<Category>('/categories', form)
    return response.data
  },

  async update(id: number, form: CategoryForm) {
    const response = await api.put<Category>(`/categories/${id}`, form)
    return response.data
  },

  async remove(id: number) {
    const response = await api.delete(`/categories/${id}`)
    return response.data
  },
}
