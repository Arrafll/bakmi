<template>
  <div
    class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 group"
  >
    <!-- Image -->
    <div class="relative overflow-hidden h-52 bg-amber-100">
      <img
        v-if="item.image_path"
        :src="asset('/storage/' + item.image_path)"
        :alt="item.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      />
      <div
        v-else
        class="w-full h-full flex items-center justify-center text-6xl"
      >
        🍜
      </div>
      <span
        class="absolute top-3 left-3 bg-amber-700 text-white text-xs font-semibold px-3 py-1 rounded-full capitalize"
      >
        {{ item.category }}
      </span>
      <span
        v-if="percentage !== null"
        class="absolute top-3 right-3 bg-amber-950/85 text-white text-xs font-bold px-3 py-1 rounded-full"
      >
        {{ rank === 1 ? '🥇' : '⭐' }} {{ percentage.toFixed(1) }}%
      </span>
    </div>

    <!-- Body -->
    <div class="p-5">
      <h2 class="text-xl font-bold text-gray-800 truncate">{{ item.name }}</h2>
      <p class="mt-1 text-gray-500 text-sm line-clamp-2 min-h-[40px]">
        {{ item.description ?? 'Menu spesial pilihan chef kami.' }}
      </p>

      <div class="mt-4 flex items-center justify-between">
        <span class="text-amber-700 font-bold text-xl">
          {{ formatPrice(item.price) }}
        </span>
        <span
          class="text-xs font-medium px-3 py-1 rounded-full"
          :class="item.is_available
            ? 'bg-green-100 text-green-700'
            : 'bg-red-100 text-red-500'"
        >
          {{ item.is_available ? 'Tersedia' : 'Habis' }}
        </span>
      </div>

      <!-- Order section -->
      <div class="mt-4">
        <!-- Default: Pesan button -->
        <div v-if="!ordering">
          <button
            v-if="item.is_available"
            @click="ordering = true"
            class="w-full bg-amber-700 hover:bg-amber-600 text-white font-semibold py-2 rounded-xl transition-colors"
          >
            <ShoppingCartIcon class="w-5 h-5 inline-block mr-2" /> Pesan
          </button>
          <button
            v-else
            disabled
            class="w-full bg-gray-200 text-gray-400 font-semibold py-2 rounded-xl cursor-not-allowed"
          >
            Tidak Tersedia
          </button>
        </div>

        <!-- Expanded: quantity selector + add to cart -->
        <div v-else class="space-y-3">
          <div class="flex items-center justify-between bg-amber-50 rounded-xl px-3 py-2">
            <span class="text-sm font-medium text-gray-700">Jumlah:</span>
            <div class="flex items-center gap-3">
              <button
                @click="decrease"
                class="w-8 h-8 flex items-center justify-center bg-amber-700 hover:bg-amber-600 text-white rounded-full font-bold text-lg transition-colors"
              >
                −
              </button>
              <span class="w-6 text-center font-bold text-lg">{{ quantity }}</span>
              <button
                @click="increase"
                class="w-8 h-8 flex items-center justify-center bg-amber-700 hover:bg-amber-600 text-white rounded-full font-bold text-lg transition-colors"
              >
                +
              </button>
            </div>
          </div>

          <div class="text-right text-sm text-gray-600">
            Subtotal: <span class="font-bold text-amber-700">{{ formatPrice(item.price * quantity) }}</span>
          </div>

          <div class="flex gap-2">
            <button
              @click="cancel"
              class="flex-1 border border-gray-300 text-gray-600 font-semibold py-2 rounded-xl hover:bg-gray-50 transition-colors text-sm"
            >
              Batal
            </button>
            <button
              @click="addToCart"
              :disabled="adding"
              class="flex-1 bg-amber-700 hover:bg-amber-600 disabled:opacity-60 text-white font-semibold py-2 rounded-xl transition-colors text-sm"
            >
              {{ adding ? '...' : '+ Keranjang' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ShoppingCartIcon } from '@heroicons/vue/24/outline'
import { useFormat } from '@/composables/useFormat'
import { asset } from '@/utils/asset'

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  percentage: {
    type: Number,
    default: null,
  },
  rank: {
    type: Number,
    default: null,
  },
})

const { formatPrice } = useFormat()

const ordering = ref(false)
const quantity = ref(1)
const adding = ref(false)

function decrease() {
  if (quantity.value > 1) quantity.value--
}

function increase() {
  if (quantity.value < 99) quantity.value++
}

function cancel() {
  ordering.value = false
  quantity.value = 1
}

function addToCart() {
  adding.value = true
  router.post(route('cart.add'), {
    menu_id: props.item.id,
    quantity: quantity.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      ordering.value = false
      quantity.value = 1
    },
    onFinish: () => {
      adding.value = false
    },
  })
}
</script>
