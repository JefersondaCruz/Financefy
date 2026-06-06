import api from './api'
import type { Transaction, Category, PaginatedResponse, TransactionForm, TransactionSummary } from '@/types/finance'

interface TransactionParams {
  page: number
  per_page: number
  start_date: string
  end_date: string
}

export const dashboardService = {
  async getTransactions(params: TransactionParams) {
    const response = await api.get<PaginatedResponse<Transaction>>('/transactions', { params })
    return response.data
  },

  async getTransactionSummary(params: Pick<TransactionParams, 'start_date' | 'end_date'>) {
    const response = await api.get<TransactionSummary>('/transactions/summary', { params })
    return response.data
  },

  async getCategories() {
    const response = await api.get<Category[]>('/categories')
    return response.data
  },

  async createTransaction(form: TransactionForm) {
    const response = await api.post<Transaction>('/transactions', form)
    return response.data
  },

  async updateTransaction(id: number, form: TransactionForm) {
    const response = await api.put<Transaction>(`/transactions/${id}`, form)
    return response.data
  },

  async deleteTransaction(id: number) {
    const response = await api.delete(`/transactions/${id}`)
    return response.data
  },
}
