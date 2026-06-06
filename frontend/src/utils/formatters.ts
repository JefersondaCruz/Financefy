export const formatCurrency = (value: number) =>
  new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Math.abs(value))

export const formatDate = (
  date: string | Date,
  options: Intl.DateTimeFormatOptions = {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  },
) => new Date(date).toLocaleDateString('pt-BR', options)

const paymentMethods = {
  pix: { label: 'Pix', icon: '⚡' },
  credit_card: { label: 'Crédito', icon: '💳' },
  money: { label: 'Dinheiro', icon: '💵' },
  others: { label: 'Outros', icon: '◦' },
} as const

export type PaymentMethod = keyof typeof paymentMethods

export const paymentMethodLabel = (method: string) =>
  paymentMethods[method as PaymentMethod]?.label ?? method

export const paymentMethodIcon = (method: string) =>
  paymentMethods[method as PaymentMethod]?.icon ?? '◦'

export const paymentMethodOptions = Object.entries(paymentMethods).map(([value, meta]) => ({
  value,
  ...meta,
}))
