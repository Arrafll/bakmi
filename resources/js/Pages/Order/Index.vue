<template>
  <CustomerLayout title="Keranjang" subtitle="Periksa pesanan Anda">
    <template #header-actions>
      <Link
        v-if="pendingReview"
        :href="route('orders.review.show', pendingReview.order_id)"
        class="flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white text-sm font-semibold px-3 py-2 rounded-xl transition-colors flex-shrink-0"
      >
        <ClipboardDocumentCheckIcon class="w-4 h-4" /> Isi Penilaian Menu
      </Link>
    </template>

    <RecommendedMenuSection :items="recommendations" />

    <div class="max-w-6xl mx-auto px-4 mt-8 flex items-center gap-3 flex-wrap">
      <div class="flex-1 min-w-0">
        <CategoryFilter :categories="categories" v-model="activeCategory" />
      </div>
      <Link :href="route('recommendation.index')"
        class="flex items-center gap-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 text-sm font-semibold px-4 py-2 rounded-xl transition-colors flex-shrink-0 border border-amber-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
        </svg>
        Rekomendasi
      </Link>
    </div>

    <!-- ── Menu Grid ─────────────────────────────────────────────────────── -->
    <main class="max-w-6xl mx-auto px-4 py-8">
      <div
        v-if="filteredMenus.length"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
      >
        <MenuCard v-for="item in filteredMenus" :key="item.id" :item="item" />
      </div>

      <div v-else class="text-center py-20 text-gray-400">
        <p class="text-5xl mb-4">🍽️</p>
        <p class="text-xl">Tidak ada menu di kategori ini.</p>
      </div>
    </main>

    <!-- ── Floating "Lihat Pesanan" sticky bar (mobile) ─────────────────── -->
    <div
      v-if="cartCount > 0"
      class="fixed bottom-0 inset-x-0 z-20 p-4 md:hidden"
    >
      <button
        @click="cartOpen = true"
        class="w-full flex items-center justify-between bg-amber-800 text-white
               font-semibold px-5 py-3 rounded-2xl shadow-xl"
      >
        <span class="flex items-center gap-2">
          <ShoppingCartIcon class="w-5 h-5" />
          Lihat Pesanan ({{ cartCount }})
        </span>
        <span>{{ formatPrice(cartTotal) }}</span>
      </button>
    </div>

    <!-- ── Cart Drawer ───────────────────────────────────────────────────── -->
    <Transition name="slide">
      <div
        v-if="cartOpen"
        class="fixed inset-0 z-30 flex justify-end"
        role="dialog"
        aria-modal="true"
        aria-label="Keranjang belanja"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/40 backdrop-blur-sm"
          @click="cartOpen = false"
        />

        <!-- Panel -->
        <div class="relative w-full max-w-md bg-white flex flex-col h-full shadow-2xl overflow-hidden">

          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 bg-amber-800 text-white flex-shrink-0">
            <h2 class="text-lg font-bold flex items-center gap-2">
              <ShoppingCartIcon class="w-5 h-5" /> Pesanan – {{ table.name }}
            </h2>
            <button @click="cartOpen = false" class="hover:opacity-75" aria-label="Tutup">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>

          <!-- Cart Items -->
          <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
            <div v-if="!cartItems.length" class="text-center text-gray-400 py-16">
              <ShoppingCartIcon class="w-12 h-12 mx-auto mb-4" />
              <p>Keranjang kosong</p>
            </div>

            <div
              v-for="item in cartItems"
              :key="item.menu_id"
              class="flex items-center gap-3 bg-white rounded-xl p-3 border border-gray-100"
            >
              <!-- Thumbnail -->
              <div class="w-14 h-14 rounded-lg overflow-hidden bg-amber-100 flex-shrink-0">
                <img
                  v-if="item.image_path"
                  :src="asset('/storage/' + item.image_path)"
                  :alt="item.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-xl">🍜</div>
              </div>

              <!-- Name & Price -->
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 truncate text-sm">{{ item.name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ formatPrice(item.price) }}</p>
              </div>

              <!-- Quantity Controls -->
              <div class="flex items-center gap-2 flex-shrink-0">
                <button
                  @click="updateCart(item.menu_id, item.quantity - 1)"
                  :disabled="updating"
                  class="w-7 h-7 flex items-center justify-center bg-amber-700 hover:bg-amber-600
                         text-white rounded-full font-bold text-lg disabled:opacity-50 transition-colors"
                >
                  −
                </button>
                <span class="w-6 text-center font-medium text-gray-800 text-sm">{{ item.quantity }}</span>
                <button
                  @click="updateCart(item.menu_id, item.quantity + 1)"
                  :disabled="updating"
                  class="w-7 h-7 flex items-center justify-center bg-amber-700 hover:bg-amber-600
                         text-white rounded-full font-bold text-lg disabled:opacity-50 transition-colors"
                >
                  +
                </button>
              </div>
            </div>
          </div>

          <!-- Summary + Voucher + Checkout Form -->
          <div v-if="cartItems.length" class="border-t border-amber-100 px-5 py-4 space-y-4 flex-shrink-0 bg-white">

            <!-- Voucher row -->
            <div class="flex gap-2">
              <input
                v-model="voucherCode"
                type="text"
                placeholder="Kode voucher (opsional)"
                maxlength="50"
                class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none
                       focus:ring-2 focus:ring-amber-400"
                @input="voucherCode = voucherCode.toUpperCase()"
              />
              <button
                @click="applyVoucher"
                :disabled="!voucherCode || applyingVoucher"
                class="px-4 py-2 bg-amber-700 hover:bg-amber-600 text-white text-sm font-medium
                       rounded-xl disabled:opacity-50 transition-colors"
              >
                Pakai
              </button>
            </div>

            <p v-if="voucherMessage" class="text-sm" :class="discountAmount > 0 ? 'text-green-600' : 'text-red-500'">
              {{ voucherMessage }}
            </p>

            <!-- Totals -->
            <div class="space-y-1 text-sm">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>{{ formatPrice(cartTotal) }}</span>
              </div>
              <div v-if="discountAmount > 0" class="flex justify-between text-green-600">
                <span>Diskon</span>
                <span>– {{ formatPrice(discountAmount) }}</span>
              </div>
              <div class="flex justify-between font-bold text-base text-gray-800 pt-1 border-t border-gray-100">
                <span>Total</span>
                <span>{{ formatPrice(Math.max(0, cartTotal - discountAmount)) }}</span>
              </div>
            </div>

            <!-- Checkout form -->
            <form @submit.prevent="placeOrder" class="space-y-3">
              <input
                v-model="form.customer_name"
                type="text"
                placeholder="Nama kamu *"
                required
                maxlength="255"
                autocomplete="name"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none
                       focus:ring-2 focus:ring-amber-400"
              />
              <p v-if="formErrors.customer_name" class="text-xs text-red-500">{{ formErrors.customer_name }}</p>

              <input
                v-model="form.customer_phone"
                type="tel"
                placeholder="No. HP *"
                required
                maxlength="20"
                autocomplete="tel"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none
                       focus:ring-2 focus:ring-amber-400"
              />
              <p v-if="formErrors.customer_phone" class="text-xs text-red-500">{{ formErrors.customer_phone }}</p>

              <textarea
                v-model="form.notes"
                placeholder="Catatan tambahan (opsional)"
                rows="2"
                maxlength="500"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm resize-none
                       focus:outline-none focus:ring-2 focus:ring-amber-400"
              />

              <button
                type="submit"
                :disabled="submitting"
                class="w-full bg-amber-700 hover:bg-amber-600 text-white font-bold py-3 rounded-xl
                       transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <span v-if="submitting">Memproses…</span>
                <span v-else>Pesan Sekarang</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <AppFooter />
</CustomerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import AppFooter from '@/Components/AppFooter.vue'
import CategoryFilter from '@/Components/CategoryFilter.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import { ShoppingCartIcon, XMarkIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline'
import MenuCard from '@/Components/MenuCard.vue'
import RecommendedMenuSection from '@/Components/RecommendedMenuSection.vue'
import { asset } from '@/utils/asset'

// ── Props (injected by server – NEVER trusted from localStorage) ──────────────
const props = defineProps({
  table: {
    type: Object,
    required: true,
  },
  menus: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: null,
  },
  recommendations: {
    type: Array,
    default: () => [],
  },
  pendingReview: {
    type: Object,
    default: null,
  },
})

// ── Shared state (cart comes from server session via HandleInertiaRequests) ───
const page = usePage()
const cartItems = computed(() => page.props.cart ?? [])
const cartCount = computed(() => cartItems.value.reduce((sum, i) => sum + i.quantity, 0))
const cartTotal = computed(() => cartItems.value.reduce((sum, i) => sum + i.price * i.quantity, 0))

// ── Local UI state ────────────────────────────────────────────────────────────
const cartOpen       = ref(false)
const activeCategory = ref('all')
const updating       = ref(false)
const submitting     = ref(false)
const applyingVoucher= ref(false)

const voucherCode    = ref('')
const voucherMessage = ref('')
const discountAmount = ref(0)
const appliedVoucher = ref('')

const form       = ref({ customer_name: '', customer_phone: '', notes: '' })
const formErrors = ref({})

// ── Derived ───────────────────────────────────────────────────────────────────
const categories = computed(() => {
  if (props.categories) {
    return ['all', ...props.categories]
  }
  const unique = [...new Set(props.menus.map(m => m.category))]
  return ['all', ...unique]
})

const filteredMenus = computed(() => {
  if (activeCategory.value === 'all') return props.menus
  const target = activeCategory.value.toLowerCase()
  return props.menus.filter(m => (m.category ?? '').toLowerCase() === target)
})

// ── Helpers ───────────────────────────────────────────────────────────────────
const formatPrice = (n) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n)

// ── Cart mutations ────────────────────────────────────────────────────────────
function updateCart(menuId, newQty) {
  updating.value = true
  const endpoint = newQty <= 0 ? route('cart.remove') : route('cart.update')

  router.post(endpoint, { menu_id: menuId, quantity: newQty }, {
    preserveScroll: true,
    onFinish: () => { updating.value = false },
  })
}

// ── Voucher ───────────────────────────────────────────────────────────────────
function applyVoucher() {
  if (!voucherCode.value) return
  applyingVoucher.value = true
  voucherMessage.value  = ''
  discountAmount.value = 0
  appliedVoucher.value = ''

  router.post(route('voucher.apply'), { voucher_code: voucherCode.value }, {
    preserveScroll: true,
    onSuccess: (page) => {
      const flash = page.props.flash ?? {}

      // Check if there's an error message
      if (flash.error) {
        voucherMessage.value = flash.error
        discountAmount.value = 0
        appliedVoucher.value = ''
      }
      // Check if voucher was successfully applied
      else if (flash.success && flash.discount !== undefined) {
        discountAmount.value = flash.discount
        appliedVoucher.value = voucherCode.value
        voucherMessage.value = flash.success
      }
      // Fallback
      else {
        voucherMessage.value = 'Terjadi kesalahan.'
        discountAmount.value = 0
        appliedVoucher.value = ''
      }
    },
    onError: () => {
      voucherMessage.value = 'Voucher tidak valid.'
      discountAmount.value = 0
      appliedVoucher.value = ''
    },
    onFinish: () => { applyingVoucher.value = false },
  })
}

// ── Order submission ──────────────────────────────────────────────────────────
function placeOrder() {
  formErrors.value = {}
  submitting.value = true

  router.post(route('order.store'), {
    customer_name:  form.value.customer_name.trim(),
    customer_phone: form.value.customer_phone.trim(),
    notes:          form.value.notes.trim(),
    voucher_code:   appliedVoucher.value || undefined,
  }, {
    preserveScroll: true,
    onError: (errors) => {
      formErrors.value = errors
      submitting.value = false
    },
    onFinish: () => { submitting.value = false },
  })
}
</script>

<style scoped>
/* Smooth slide-in for cart drawer */
.slide-enter-active,
.slide-leave-active {
  transition: opacity 0.25s ease;
}
.slide-enter-active .relative,
.slide-leave-active .relative {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from,
.slide-leave-to {
  opacity: 0;
}
.slide-enter-from .relative {
  transform: translateX(100%);
}
.slide-leave-to .relative {
  transform: translateX(100%);
}

/* Hide scrollbar on category strip */
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
