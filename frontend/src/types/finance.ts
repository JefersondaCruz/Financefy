export interface Category {
  id: number
  name: string
  type: 'income' | 'expense'
  user_id?: number | null
}

export interface CategoryForm {
  name: string
  type: 'income' | 'expense'
}

export interface Transaction {
  id: number
  description: string
  amount: number
  transaction_date: string
  payment_method: string
  category_id: number | null
  category: Category | null
  is_recurring: boolean
  recurrence_type: string | null
}

export interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  per_page: number
  total: number
  last_page: number
}

export interface TransactionForm {
  category_id: number | null
  description: string
  amount: number
  transaction_date: string
  payment_method: string
  is_recurring: boolean
  recurrence_type: string | null
}

export interface Goal {
  id: number
  name: string
  icon: string
  target_amount: number
  current_amount: number
  deadline?: string | null
}

export interface GoalForm {
  name: string
  icon: string
  target_amount: number
  current_amount: number
  deadline: string
}

export interface DateFilter {
  month?: number
  year?: number
  start_date: string
  end_date: string
}
