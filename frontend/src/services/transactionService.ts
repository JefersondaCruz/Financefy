import api from './api'
import type { PaginatedResponse, Transaction, TransactionForm, TransactionSummary } from '@/types/finance'

export interface TransactionParams {
  page?: number
  per_page?: number
  start_date: string
  end_date: string
  search?: string
  type?: string
  category_id?: number
  payment_method?: string
  recurring?: string
}

export const transactionService = {
  async list(params: TransactionParams) {
    const response = await api.get<PaginatedResponse<Transaction>>('/transactions', { params })
    return response.data
  },

  async summary(params: TransactionParams) {
    const response = await api.get<TransactionSummary>('/transactions/summary', { params })
    return response.data
  },

  async exportCsv(params: TransactionParams) {
    const response = await api.get<Blob>('/transactions/export', {
      params,
      responseType: 'blob',
    })
    return response.data
  },

  async create(form: TransactionForm) {
    const response = await api.post<Transaction>('/transactions', form)
    return response.data
  },

  async update(id: number, form: TransactionForm) {
    const response = await api.put<Transaction>(`/transactions/${id}`, form)
    return response.data
  },

  async remove(id: number) {
    const response = await api.delete(`/transactions/${id}`)
    return response.data
  },
}
