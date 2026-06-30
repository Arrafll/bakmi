<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-amber-800 text-white px-4 py-4 flex items-center gap-3 sticky top-0 z-10">
      <button @click="goBack" class="p-1.5 rounded-lg hover:bg-amber-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <div>
        <h1 class="font-bold text-base leading-tight">Menu Pilihan</h1>
        <p class="text-amber-300 text-xs">Pilihan terbaik untuk Anda</p>
      </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-6">

      <!-- Empty state -->
      <div v-if="ranking.length === 0" class="text-center py-12">
        <div class="text-4xl mb-3">🍜</div>
        <p class="text-gray-500 text-sm">Belum ada menu yang tersedia.</p>
      </div>

      <!-- Ranking cards -->
      <div v-else class="space-y-4">
        <div v-for="r in ranking" :key="r.menu.id"
          class="bg-white rounded-2xl shadow-sm overflow-hidden border"
          :class="r.rank === 1 ? 'border-amber-300 ring-2 ring-amber-200' : 'border-gray-100'">

          <!-- Rank ribbon -->
          <div :class="[
            'px-4 py-2 flex items-center justify-between',
            r.rank === 1 ? 'bg-amber-500' :
            r.rank === 2 ? 'bg-gray-400' :
            r.rank === 3 ? 'bg-orange-400' : 'bg-gray-200',
          ]">
            <span :class="['font-bold text-sm', r.rank <= 3 ? 'text-white' : 'text-gray-600']">
              {{ r.rank === 1 ? '🥇' : r.rank === 2 ? '🥈' : r.rank === 3 ? '🥉' : '#' + r.rank }}
              Pilihan {{ r.rank === 1 ? 'Terbaik' : 'ke-' + r.rank }}
            </span>
          </div>

          <!-- Menu details -->
          <div class="flex">
            <div class="w-28 h-28 shrink-0 bg-gray-100">
              <img v-if="r.menu.image_path"
                :src="'/storage/' + r.menu.image_path"
                :alt="r.menu.name"
                class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-3xl">🍜</div>
            </div>

            <div class="flex-1 p-4 min-w-0">
              <h3 class="font-bold text-gray-800 text-sm">{{ r.menu.name }}</h3>
              <p class="text-xs text-gray-500 capitalize mt-0.5">{{ r.menu.category }}</p>
              <p v-if="r.menu.description" class="text-xs text-gray-400 mt-1 line-clamp-2">{{ r.menu.description }}</p>
              <div class="mt-2 flex items-center justify-between">
                <span class="text-amber-700 font-bold text-sm">{{ formatPrice(r.menu.price) }}</span>
                <button @click="addToCart(r.menu)"
                  class="bg-amber-700 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                  + Pesan
                </button>
              </div>
            </div>
          </div>

          <!-- Score bar -->
          <div class="px-4 pb-3">
            <div class="flex items-center justify-between mb-1">
              <span class="text-xs text-gray-400">Skor kecocokan</span>
              <span class="text-xs font-bold"
                :class="r.rank === 1 ? 'text-amber-600' : r.rank === 2 ? 'text-gray-500' : r.rank === 3 ? 'text-orange-500' : 'text-gray-400'">
                {{ (r.score * 100).toFixed(1) }}%
              </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div class="h-2 rounded-full transition-all"
                :class="r.rank === 1 ? 'bg-amber-500' : r.rank === 2 ? 'bg-gray-400' : r.rank === 3 ? 'bg-orange-400' : 'bg-gray-300'"
                :style="{ width: (r.score * 100).toFixed(1) + '%' }">
              </div>
            </div>
          </div>
        </div>
      </div>

      <button @click="goBack"
        class="mt-6 w-full py-3 border border-amber-300 text-amber-700 font-semibold text-sm rounded-xl hover:bg-amber-50 transition-colors">
        Kembali ke Menu
      </button>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  ranking:  { type: Array, default: () => [] },
  criteria: { type: Array, default: () => [] },
})

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function goBack() {
  window.history.back()
}

function addToCart(menu) {
  router.post(route('cart.add'), { menu_id: menu.id, quantity: 1 }, {
    preserveScroll: true,
    onSuccess: () => { goBack() },
  })
}
</script>
