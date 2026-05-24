<template>
  <div class="min-h-screen bg-amber-50">
    <AppHeader title="✅ Pesanan Berhasil" />

    <main class="max-w-2xl mx-auto px-4 py-10">
      <div class="bg-white rounded-2xl shadow-md p-8">
        <!-- Success header -->
        <div class="text-center mb-8">
          <div class="text-6xl mb-4">🎉</div>
          <h2 class="text-2xl font-bold text-gray-800 mb-2">Pesanan Diterima!</h2>
          <p class="text-gray-500">
            Terima kasih, <strong>{{ order.customer_name }}</strong>.
            Pesanan Anda sedang diproses.
          </p>
        </div>

        <!-- Order number -->
        <div class="bg-amber-50 rounded-xl px-5 py-4 mb-6 flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide">No. Pesanan</p>
            <p class="font-bold text-amber-700 text-xl">#{{ String(order.id).padStart(5, '0') }}</p>
          </div>
          <div class="text-right">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Status</p>
            <span class="inline-block mt-1 bg-yellow-100 text-yellow-700 text-sm font-semibold px-3 py-1 rounded-full capitalize">
              {{ order.status }}
            </span>
          </div>
        </div>

        <!-- Order items -->
        <div class="divide-y divide-gray-100 mb-6">
          <div
            v-for="item in order.items"
            :key="item.id"
            class="py-3 flex items-center justify-between"
          >
            <div>
              <span class="font-medium text-gray-800">{{ item.menu?.name ?? 'Item' }}</span>
              <span class="text-gray-400 text-sm ml-2">× {{ item.quantity }}</span>
            </div>
            <span class="font-semibold text-gray-800">{{ formatPrice(item.price * item.quantity) }}</span>
          </div>
        </div>

        <!-- Discount row -->
        <div v-if="order.voucher_code" class="flex items-center justify-between border-t border-gray-100 pt-3">
          <span class="text-sm text-green-700 flex items-center gap-1">🎟️ Voucher {{ order.voucher_code }}</span>
          <span class="text-sm font-semibold text-green-700">− {{ formatPrice(order.discount_amount) }}</span>
        </div>

        <!-- Total -->
        <div class="flex items-center justify-between border-t border-gray-100 pt-4 mb-8">
          <span class="font-bold text-gray-700 text-lg">Total</span>
          <span class="text-2xl font-bold text-amber-700">{{ formatPrice(order.total_price) }}</span>
        </div>

        <!-- Customer info -->
        <div v-if="order.notes" class="bg-gray-50 rounded-xl px-4 py-3 mb-6 text-sm text-gray-600">
          <span class="font-medium">Catatan:</span> {{ order.notes }}
        </div>

        <!-- CTA -->
        <Link
          :href="route('home')"
          class="w-full block text-center bg-amber-700 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition-colors text-lg"
        >
          Pesan Lagi
        </Link>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
})

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}
</script>
