import api from './api'
import type { Transaction, Category, PaginatedResponse, TransactionForm } from '@/types/finance'

interface TransactionParams {
  page: number
  per_page: number
  start_date: string
  end_date: string
}

export const dashboardService = {
  getTransactions(params: TransactionParams) {
    return api.get<PaginatedResponse<Transaction>>('/transactions', { params })
  },

  getCategories() {
    return api.get<Category[]>('/categories')
  },

  createTransaction(form: TransactionForm) {
    return api.post<Transaction>('/transactions', form)
  },

  updateTransaction(id: number, form: TransactionForm) {
    return api.put<Transaction>(`/transactions/${id}`, form)
  },

  deleteTransaction(id: number) {
    return api.delete(`/transactions/${id}`)
  },
}
