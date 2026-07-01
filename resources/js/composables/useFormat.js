import { usePage } from '@inertiajs/vue3'

export function useFormat() {
  const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(price)
  }

  const formatDate = (date) => {
    if (!date) return '-'
    const d = new Date(date)
    const day = d.getDate()
    const month = d.toLocaleString('id-ID', { month: 'long' })
    const year = d.getFullYear()
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    return `${day} ${month} ${year} pukul ${hours}.${minutes}`
  }

  const formatTime = (date) => {
    if (!date) return '-'
    return new Intl.DateTimeFormat('id-ID', {
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(date))
  }

  return {
    formatPrice,
    formatDate,
    formatTime,
  }
}
