<template>
  <div class="min-h-screen bg-amber-50">
    <AppHeader title="Keranjang" subtitle="Periksa pesanan Anda" />

    <main class="max-w-4xl mx-auto px-4 py-10">
      <!-- Empty cart -->
      <div v-if="cart.length === 0" class="text-center py-20 text-gray-400">
        <p class="text-6xl mb-4">🛒</p>
        <p class="text-xl mb-6">Keranjang Anda kosong</p>
        <Link
          :href="route('home')"
          class="bg-amber-700 hover:bg-amber-600 text-white font-semibold px-6 py-3 rounded-xl transition-colors inline-block"
        >
          Lihat Menu
        </Link>
      </div>

      <div v-else class="space-y-6">
        <!-- Cart items -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">Item Pesanan</h2>
            <button
              @click="clearCart"
              class="text-sm text-red-500 hover:text-red-700 transition-colors"
            >
              Kosongkan
            </button>
          </div>

          <div class="divide-y divide-gray-100">
            <div
              v-for="item in cart"
              :key="item.menu_id"
              class="p-4 flex items-center gap-4"
            >
              <!-- Thumbnail -->
              <div class="w-16 h-16 rounded-xl overflow-hidden bg-amber-100 flex-shrink-0">
                <img
                  v-if="item.image_path"
                  :src="'/storage/' + item.image_path"
                  :alt="item.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-2xl">🍜</div>
              </div>

              <!-- Name & price -->
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 truncate">{{ item.name }}</p>
                <p class="text-amber-700 text-sm">{{ formatPrice(item.price) }} / porsi</p>
              </div>

              <!-- Quantity controls -->
              <div class="flex items-center gap-2 flex-shrink-0">
                <button
                  @click="updateQuantity(item, item.quantity - 1)"
                  class="w-8 h-8 flex items-center justify-center bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-full font-bold transition-colors"
                >
                  −
                </button>
                <span class="w-8 text-center font-bold text-lg">{{ item.quantity }}</span>
                <button
                  @click="updateQuantity(item, item.quantity + 1)"
                  class="w-8 h-8 flex items-center justify-center bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-full font-bold transition-colors"
                >
                  +
                </button>
              </div>

              <!-- Subtotal & remove -->
              <div class="w-28 text-right flex-shrink-0">
                <p class="font-bold text-gray-800">{{ formatPrice(item.price * item.quantity) }}</p>
                <button
                  @click="removeItem(item)"
                  class="text-xs text-red-400 hover:text-red-600 mt-1 transition-colors"
                >
                  Hapus
                </button>
              </div>
            </div>
          </div>

          <!-- Total -->
          <!-- Total row + voucher summary -->
          <div class="px-5 py-4 border-t border-gray-100 bg-amber-50 space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Subtotal</span>
              <span class="text-base font-semibold text-gray-700">{{ formatPrice(subtotal) }}</span>
            </div>
            <div v-if="voucher.applied" class="flex items-center justify-between text-green-700">
              <span class="text-sm flex items-center gap-1">🎟️ {{ voucher.code }}</span>
              <span class="text-sm font-semibold">− {{ formatPrice(voucher.discountAmount) }}</span>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-amber-200">
              <span class="font-bold text-gray-700 text-lg">Total</span>
              <span class="text-2xl font-bold text-amber-700">{{ formatPrice(grandTotal) }}</span>
            </div>
          </div>
        </div>

        <!-- Voucher code -->
        <div class="bg-white rounded-2xl shadow-md p-6">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Kode Voucher</h2>

          <div v-if="voucher.applied" class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3">
            <div>
              <p class="text-green-700 font-semibold text-sm">🎟️ {{ voucher.code }} diterapkan!</p>
              <p class="text-green-600 text-xs mt-0.5">{{ voucher.description }} — hemat {{ formatPrice(voucher.discountAmount) }}</p>
            </div>
            <button @click="removeVoucher" class="text-xs text-red-500 hover:text-red-700 font-medium ml-4">Hapus</button>
          </div>

          <div v-else class="flex gap-2">
            <input
              v-model="voucher.input"
              type="text"
              placeholder="Masukkan kode voucher"
              class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition uppercase"
              @keyup.enter="applyVoucher"
            />
            <button
              @click="applyVoucher"
              :disabled="voucher.loading || !voucher.input.trim()"
              class="bg-amber-700 hover:bg-amber-600 disabled:opacity-60 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors"
            >
              {{ voucher.loading ? '...' : 'Terapkan' }}
            </button>
          </div>
          <p v-if="voucher.error" class="text-red-500 text-sm mt-2">{{ voucher.error }}</p>
        </div>

        <!-- Order form -->
        <div class="bg-white rounded-2xl shadow-md p-6">
          <h2 class="text-lg font-bold text-gray-800 mb-5">Data Pemesan</h2>

          <div v-if="errors.cart" class="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3">
            {{ errors.cart }}
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
              <input
                v-model="form.customer_name"
                type="text"
                placeholder="Masukkan nama Anda"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 transition"
              />
              <p v-if="errors.customer_name" class="text-red-500 text-sm mt-1">{{ errors.customer_name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon <span class="text-red-500">*</span></label>
              <input
                v-model="form.customer_phone"
                type="text"
                placeholder="Contoh: 08123456789"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 transition"
              />
              <p v-if="errors.customer_phone" class="text-red-500 text-sm mt-1">{{ errors.customer_phone }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
              <textarea
                v-model="form.notes"
                placeholder="Contoh: tanpa bawang, level pedas, dll."
                rows="3"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 transition resize-none"
              ></textarea>
            </div>
          </div>

          <button
            @click="placeOrder"
            :disabled="submitting"
            class="w-full mt-6 bg-amber-700 hover:bg-amber-600 disabled:opacity-60 text-white font-bold py-3 rounded-xl transition-colors text-lg"
          >
            {{ submitting ? 'Memproses...' : 'Pesan Sekarang' }}
          </button>
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

const props = defineProps({
  cart: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const errors = computed(() => page.props.errors ?? {})

const form = ref({
  customer_name: '',
  customer_phone: '',
  notes: '',
})

const submitting = ref(false)

const subtotal = computed(() =>
  props.cart.reduce((sum, item) => sum + item.price * item.quantity, 0)
)

// Voucher state
const voucher = ref({
  input: '',
  loading: false,
  applied: false,
  error: '',
  code: '',
  description: '',
  discountAmount: 0,
})

const grandTotal = computed(() => Math.max(0, subtotal.value - voucher.value.discountAmount))

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

async function applyVoucher() {
  const code = voucher.value.input.trim()
  if (!code) return

  voucher.value.loading = true
  voucher.value.error = ''

  try {
    const res = await fetch(route('voucher.apply'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
        'Accept': 'application/json',
      },
      body: JSON.stringify({ code, cart_total: subtotal.value }),
    })

    const data = await res.json()

    if (!res.ok) {
      voucher.value.error = data.error ?? 'Voucher tidak valid.'
    } else {
      voucher.value.applied = true
      voucher.value.code = data.code
      voucher.value.description = data.description ?? ''
      voucher.value.discountAmount = data.discount_amount
      voucher.value.error = ''
    }
  } catch {
    voucher.value.error = 'Gagal menghubungi server. Coba lagi.'
  } finally {
    voucher.value.loading = false
  }
}

function removeVoucher() {
  voucher.value = {
    input: '',
    loading: false,
    applied: false,
    error: '',
    code: '',
    description: '',
    discountAmount: 0,
  }
}

function getCookie(name) {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift())
  return ''
}

function updateQuantity(item, newQty) {
  if (newQty < 1 || newQty > 99) return
  router.post(route('cart.update'), {
    menu_id: item.menu_id,
    quantity: newQty,
  }, { preserveScroll: true })
}

function removeItem(item) {
  router.post(route('cart.remove'), { menu_id: item.menu_id }, { preserveScroll: true })
}

function clearCart() {
  router.post(route('cart.clear'), {}, { preserveScroll: true })
}

function placeOrder() {
  submitting.value = true
  router.post(route('order.store'), {
    ...form.value,
    voucher_code: voucher.value.applied ? voucher.value.code : '',
  }, {
    onFinish: () => {
      submitting.value = false
    },
  })
}
</script>
