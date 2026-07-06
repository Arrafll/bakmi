<template>
  <AdminLayout title="Menu Rekomendasi">
    <!-- Empty state -->
    <div v-if="rows.length === 0" class="bg-white rounded-2xl shadow-sm p-10 text-center">
      <p class="text-4xl mb-3">✨</p>
      <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum ada peringkat menu</h3>
      <p class="text-sm text-gray-500 mb-6 max-w-md mx-auto">
        Pastikan kriteria penilaian sudah aktif dan setidaknya satu menu sudah memiliki cukup data — kuesioner pelanggan untuk kriteria umum, dan riwayat pemesanan untuk Popularitas.
      </p>
      <div class="flex justify-center gap-3">
        <Link :href="route('admin.criteria.index')" class="inline-block border border-gray-300 text-gray-600 text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
          Kelola Kriteria
        </Link>
        <Link :href="route('admin.menu-scores.index')" class="inline-block bg-amber-700 hover:bg-amber-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
          Lihat Skor Menu
        </Link>
      </div>
    </div>

    <template v-else>
      <!-- Top 3 spotlight -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div
          v-for="row in rows.slice(0, 3)"
          :key="row.menu.id"
          class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4 border-2"
          :class="row.rank === 1 ? 'border-amber-400' : 'border-transparent'"
        >
          <div class="w-14 h-14 rounded-xl overflow-hidden bg-amber-100 flex items-center justify-center text-2xl flex-shrink-0">
            <img v-if="row.menu.image_path" :src="asset('/storage/' + row.menu.image_path)" :alt="row.menu.name" class="w-full h-full object-cover" />
            <span v-else>🍜</span>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-amber-700 mb-0.5">{{ medal(row.rank) }} Peringkat {{ row.rank }}</p>
            <p class="font-bold text-gray-800 truncate">{{ row.menu.name }}</p>
            <p class="text-sm text-gray-500">Skor {{ formatPercent(row.percentage) }}</p>
            <p v-if="ratings[row.menu.id]" class="text-xs text-amber-600 mt-0.5">
              ⭐ {{ Number(ratings[row.menu.id].average).toFixed(1) }} rating pelanggan
            </p>
          </div>
        </div>
      </div>

      <!-- Full ranking table -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="text-base font-semibold text-gray-700">Peringkat Lengkap</h2>
          <p class="text-xs text-gray-400 mt-0.5">Klik sebuah menu untuk melihat rincian skor per kriteria.</p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
                <th class="px-5 py-3 w-16">Peringkat</th>
                <th class="px-5 py-3">Menu</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 w-64">Skor Akhir</th>
                <th class="px-5 py-3 w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <template v-for="row in rows" :key="row.menu.id">
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" @click="toggleExpand(row.menu.id)">
                  <td class="px-5 py-3 font-semibold text-gray-700">{{ medal(row.rank) }} {{ row.rank }}</td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-lg overflow-hidden bg-amber-100 flex items-center justify-center text-lg flex-shrink-0">
                        <img v-if="row.menu.image_path" :src="asset('/storage/' + row.menu.image_path)" :alt="row.menu.name" class="w-full h-full object-cover" />
                        <span v-else>🍜</span>
                      </div>
                      <div class="min-w-0">
                        <p class="font-medium text-gray-800 truncate max-w-[220px]">{{ row.menu.name }}</p>
                        <p class="text-gray-400 text-xs capitalize">{{ row.menu.category }}</p>
                        <p v-if="ratings[row.menu.id]" class="text-[11px] text-amber-600">
                          ⭐ {{ Number(ratings[row.menu.id].average).toFixed(1) }} ({{ ratings[row.menu.id].count }} rating pelanggan)
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3">
                    <span :class="row.menu.is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded-full text-xs font-medium">
                      {{ row.menu.is_available ? 'Tersedia' : 'Habis' }}
                    </span>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                      <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-amber-500" :style="{ width: row.percentage + '%' }" />
                      </div>
                      <span class="font-semibold text-amber-700 text-sm w-16 text-right">{{ formatPercent(row.percentage) }}</span>
                    </div>
                  </td>
                  <td class="px-5 py-3 text-gray-400 text-center">
                    {{ expanded.has(row.menu.id) ? '▲' : '▼' }}
                  </td>
                </tr>
                <tr v-if="expanded.has(row.menu.id)">
                  <td :colspan="5" class="bg-amber-50/50 px-5 py-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Rincian Skor per Kriteria</p>
                    <div class="overflow-x-auto">
                      <table class="w-full text-xs">
                        <thead>
                          <tr class="text-left text-gray-500">
                            <th class="pr-4 py-1">Kriteria</th>
                            <th class="pr-4 py-1">Nilai Menu</th>
                            <th class="pr-4 py-1">Nilai Sebanding</th>
                            <th class="pr-4 py-1">Bobot</th>
                            <th class="pr-4 py-1">Kontribusi ke Skor</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-100">
                          <tr v-for="c in criteria" :key="c.id">
                            <td class="pr-4 py-1.5 font-medium text-gray-700">{{ c.name }}</td>
                            <td class="pr-4 py-1.5 text-gray-600">{{ row.raw[c.id] }} · {{ c.scale_labels[row.raw[c.id] - 1] }}</td>
                            <td class="pr-4 py-1.5 text-gray-600">{{ formatPercent(row.comparable[c.id] * 100) }}</td>
                            <td class="pr-4 py-1.5 text-gray-600">{{ c.weight }}%</td>
                            <td class="pr-4 py-1.5 font-medium text-amber-700">{{ formatPercent(row.contribution[c.id] * 100) }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Unscored menus notice -->
      <div v-if="unscoredMenus.length > 0" class="mt-6 bg-white rounded-2xl shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-1">{{ unscoredMenus.length }} menu belum masuk peringkat</p>
        <p class="text-xs text-gray-400 mb-3">Menu berikut belum memiliki cukup data kuesioner pelanggan di semua kriteria aktif, sehingga belum bisa dibandingkan secara adil.</p>
        <div class="flex flex-wrap gap-2 mb-4">
          <span v-for="m in unscoredMenus" :key="m.id" class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-xs">{{ m.name }}</span>
        </div>
        <Link :href="route('admin.menu-scores.index')" class="text-sm font-semibold text-amber-700 hover:text-amber-600">
          Lihat detail skor menu →
        </Link>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { menuImage } from '@/utils/asset'

defineProps({
  criteria: { type: Array, default: () => [] },
  rows: { type: Array, default: () => [] },
  unscoredMenus: { type: Array, default: () => [] },
  ratings: { type: Object, default: () => ({}) },
})

const expanded = ref(new Set())

function toggleExpand(menuId) {
  const next = new Set(expanded.value)
  if (next.has(menuId)) {
    next.delete(menuId)
  } else {
    next.add(menuId)
  }
  expanded.value = next
}

function medal(rank) {
  return { 1: '🥇', 2: '🥈', 3: '🥉' }[rank] ?? ''
}

function formatPercent(value) {
  return `${Number(value).toFixed(1)}%`
}
</script>
