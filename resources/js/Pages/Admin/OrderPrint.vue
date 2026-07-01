<template>
  <div class="min-h-screen bg-white p-8">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8 border-b-2 border-black pb-6">
        <h1 class="text-3xl font-bold text-gray-900">Bakmi Jawa Mas Agus</h1>
        <p class="text-sm text-gray-600 mt-1">Jl. Contoh No.123, Kota Contoh</p>
        <p class="text-sm text-gray-600">Telp: 0812-3456-7890</p>
      </div>

      <!-- Order Info -->
      <div class="mb-6 pb-4 border-b border-gray-300">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-500">No. Pesanan</p>
            <p class="font-bold text-lg">#{{ order.id }}</p>
          </div>
          <div>
            <p class="text-gray-500">Tanggal</p>
            <p class="font-semibold">{{ formatDate(order.created_at) }}</p>
          </div>
          <div>
            <p class="text-gray-500">Pelanggan</p>
            <p class="font-semibold">{{ order.customer_name }}</p>
          </div>
          <div>
            <p class="text-gray-500">No. HP</p>
            <p class="font-semibold">{{ order.customer_phone }}</p>
          </div>
          <div>
            <p class="text-gray-500">Meja</p>
            <p class="font-semibold">{{ order.table?.name ?? '—' }}</p>
          </div>
          <div>
            <p class="text-gray-500">Status</p>
            <p class="font-semibold">{{ statusLabel(order.status) }}</p>
          </div>
        </div>
        <div v-if="order.notes" class="mt-3">
          <p class="text-gray-500 text-sm">Catatan</p>
          <p class="font-semibold">{{ order.notes }}</p>
        </div>
      </div>

      <!-- Order Items -->
      <div class="mb-6">
        <h2 class="text-lg font-bold mb-3">Detail Pesanan</h2>
        <table class="w-full text-sm">
          <thead class="border-b-2 border-gray-400">
            <tr>
              <th class="text-left py-2">Item</th>
              <th class="text-center py-2">Qty</th>
              <th class="text-right py-2">Harga</th>
              <th class="text-right py-2">Subtotal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="item in order.items" :key="item.id">
              <td class="py-2">{{ item.menu?.name ?? 'Menu dihapus' }}</td>
              <td class="text-center py-2">{{ item.quantity }}</td>
              <td class="text-right py-2">{{ formatPrice(item.price) }}</td>
              <td class="text-right py-2 font-semibold">{{ formatPrice(item.price * item.quantity) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totals -->
      <div class="border-t-2 border-black pt-4 mb-8">
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">Subtotal</span>
            <span class="font-semibold">{{ formatPrice(calculateSubtotal()) }}</span>
          </div>
          <div v-if="order.voucher_code" class="flex justify-between text-green-700">
            <span>Diskon ({{ order.voucher_code }})</span>
            <span class="font-semibold">– {{ formatPrice(order.discount_amount) }}</span>
          </div>
          <div class="flex justify-between text-xl font-bold border-t border-gray-300 pt-2 mt-2">
            <span>Total</span>
            <span>{{ formatPrice(order.total_price) }}</span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center text-sm text-gray-600 border-t border-gray-300 pt-4">
        <p class="font-semibold">Terima kasih atas kunjungan Anda!</p>
        <p class="mt-1">Silakan datang kembali</p>
      </div>

      <!-- Print Button (hidden when printing) -->
      <div class="mt-8 text-center print:hidden">
        <button
          @click="handlePrint"
          class="px-6 py-3 bg-amber-700 text-white rounded-lg font-semibold hover:bg-amber-600 transition-colors"
        >
          Cetak Struk
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useFormat } from '@/composables/useFormat'

const props = defineProps({
  order: { type: Object, required: true },
})

const { formatPrice, formatDate } = useFormat()

function calculateSubtotal() {
  return props.order.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
}

function statusLabel(status) {
  return {
    dipesan: 'Dipesan',
    diproses: 'Diproses',
    selesai: 'Selesai',
    dibatalkan: 'Dibatalkan',
  }[status] ?? status
}

function handlePrint() {
  window.print()
}
</script>

<style>
@media print {
  body {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }

  @page {
    size: A4;
    margin: 1cm;
  }
}
</style>
