<template>
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Laporan Transaksi</h1>
        <button
          @click="showExportModal = true"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export Excel
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filter</h2>
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input
              type="date"
              v-model="filterForm.start_date"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input
              type="date"
              v-model="filterForm.end_date"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
            <select
              v-model="filterForm.payment_method"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
            >
              <option value="">Semua</option>
              <option value="cash">Cash</option>
              <option value="qris">QRIS</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Meja</label>
            <select
              v-model="filterForm.table_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
            >
              <option value="">Semua</option>
              <option v-for="table in tables" :key="table.id" :value="table.id">
                {{ table.name }}
              </option>
            </select>
          </div>
          <div class="md:col-span-4 flex gap-2">
            <button
              type="submit"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg"
            >
              Terapkan Filter
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg"
            >
              Reset
            </button>
          </div>
        </form>
      </div>

      <!-- Total Revenue Card -->
      <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-100 text-sm font-medium">Total Pendapatan</p>
            <p class="text-3xl font-bold mt-2">{{ formatCurrency(totalRevenue) }}</p>
          </div>
          <div class="bg-white bg-opacity-20 rounded-full p-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  No. Order
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Meja
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tanggal
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Item Pesanan
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Voucher
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Diskon
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Harga Final
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Pembayaran
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  ORD-{{ transaction.id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ transaction.table?.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(transaction.created_at) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                  <div class="line-clamp-2">
                    {{ formatOrderItems(transaction.items) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <span v-if="transaction.voucher_code" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    {{ transaction.voucher_code }}
                  </span>
                  <span v-else>-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                  {{ formatCurrency(transaction.discount_amount || 0) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                  {{ formatCurrency(transaction.total_price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="paymentMethodClass(transaction.payment_method)" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ formatPaymentMethod(transaction.payment_method || 'cash') }}
                  </span>
                </td>
              </tr>
              <tr v-if="transactions.data.length === 0">
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                  Tidak ada transaksi
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="transactions.data.length > 0" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Menampilkan {{ transactions.from }} - {{ transactions.to }} dari {{ transactions.total }} transaksi
            </div>
            <div class="flex gap-2">
              <button
                v-for="link in transactions.links"
                :key="link.label"
                @click="changePage(link.url)"
                :disabled="!link.url"
                :class="[
                  'px-3 py-1 rounded-lg text-sm',
                  link.active
                    ? 'bg-indigo-600 text-white'
                    : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Export Modal -->
      <div
        v-if="showExportModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showExportModal = false"
      >
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Export Laporan ke Excel</h2>
            <button
              @click="showExportModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="exportToExcel" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
              <input
                type="date"
                v-model="exportForm.start_date"
                class="w-full border border-gray-300 rounded-lg px-3 py-2"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
              <input
                type="date"
                v-model="exportForm.end_date"
                class="w-full border border-gray-300 rounded-lg px-3 py-2"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
              <select
                v-model="exportForm.payment_method"
                class="w-full border border-gray-300 rounded-lg px-3 py-2"
              >
                <option value="">Semua</option>
                <option value="cash">Cash</option>
                <option value="qris">QRIS</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Meja</label>
              <select
                v-model="exportForm.table_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2"
              >
                <option value="">Semua</option>
                <option v-for="table in tables" :key="table.id" :value="table.id">
                  {{ table.name }}
                </option>
              </select>
            </div>

            <div class="flex gap-3 pt-4">
              <button
                type="submit"
                :disabled="exporting"
                class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-2 rounded-lg font-medium"
              >
                {{ exporting ? 'Mengexport...' : 'Export' }}
              </button>
              <button
                type="button"
                @click="showExportModal = false"
                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium"
              >
                Batal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useFormat } from '@/composables/useFormat'

const props = defineProps({
  transactions: Object,
  totalRevenue: Number,
  tables: Array,
  filters: Object
})

const { formatCurrency } = useFormat()

const showExportModal = ref(false)
const exporting = ref(false)

const filterForm = reactive({
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
  payment_method: props.filters?.payment_method || '',
  table_id: props.filters?.table_id || ''
})

const exportForm = reactive({
  start_date: '',
  end_date: '',
  payment_method: '',
  table_id: ''
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatOrderItems = (items) => {
  return items.map(item => `${item.menu.name} x${item.quantity}`).join(', ')
}

const formatPaymentMethod = (method) => {
  return method === 'cash' ? 'Cash' : 'QRIS'
}

const paymentMethodClass = (method) => {
  return method === 'cash'
    ? 'bg-green-100 text-green-800'
    : 'bg-blue-100 text-blue-800'
}

const applyFilters = () => {
  router.get(route('admin.reports'), filterForm, {
    preserveState: true,
    preserveScroll: true
  })
}

const resetFilters = () => {
  filterForm.start_date = ''
  filterForm.end_date = ''
  filterForm.payment_method = ''
  filterForm.table_id = ''
  applyFilters()
}

const changePage = (url) => {
  if (!url) return
  router.get(url, {}, {
    preserveState: true,
    preserveScroll: true
  })
}

const exportToExcel = () => {
  exporting.value = true

  const form = document.createElement('form')
  form.method = 'POST'
  form.action = route('admin.reports.export')

  // Add CSRF token
  const csrfInput = document.createElement('input')
  csrfInput.type = 'hidden'
  csrfInput.name = '_token'
  csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  form.appendChild(csrfInput)

  // Add filters
  Object.keys(exportForm).forEach(key => {
    if (exportForm[key]) {
      const input = document.createElement('input')
      input.type = 'hidden'
      input.name = key
      input.value = exportForm[key]
      form.appendChild(input)
    }
  })

  document.body.appendChild(form)
  form.submit()
  document.body.removeChild(form)

  setTimeout(() => {
    exporting.value = false
    showExportModal.value = false
  }, 1000)
}
</script>
