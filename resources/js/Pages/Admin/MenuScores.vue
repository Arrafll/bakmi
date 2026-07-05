<template>
  <AdminLayout title="Penilaian Menu">
    <!-- Empty state: no active criteria yet -->
    <div v-if="criteria.length === 0" class="bg-white rounded-2xl shadow-sm p-10 text-center">
      <p class="text-4xl mb-3">🧭</p>
      <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum ada kriteria penilaian aktif</h3>
      <p class="text-sm text-gray-500 mb-6 max-w-md mx-auto">
        Tambahkan kriteria penilaian terlebih dahulu (misalnya Harga, Rasa, Popularitas) sebelum skor bisa dihitung untuk tiap menu.
      </p>
      <Link :href="route('admin.criteria.index')" class="inline-block bg-amber-700 hover:bg-amber-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
        Kelola Kriteria Penilaian
      </Link>
    </div>

    <template v-else>
      <!-- Header bar -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div class="flex items-center gap-3">
          <p class="text-sm text-gray-500">{{ menus.length }} menu</p>
          <span
            class="text-xs font-semibold px-3 py-1 rounded-full"
            :class="completeCount === menus.length ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
          >
            {{ completeCount }} dari {{ menus.length }} menu memiliki skor lengkap
          </span>
        </div>
        <div class="flex gap-2">
          <input
            v-model="search"
            type="text"
            placeholder="Cari menu..."
            class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition"
          />
          <select v-model="categoryFilter" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
      </div>

      <p class="text-xs text-gray-400 mb-4">
        Semua skor dihitung otomatis — kriteria umum dari rata-rata kuesioner pelanggan, Popularitas dari frekuensi pemesanan. Tidak ada input manual.
      </p>

      <!-- Matrix -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
                <th class="px-5 py-3 sticky left-0 bg-gray-50 z-10 min-w-[220px]">Menu</th>
                <th v-for="c in criteria" :key="c.id" class="px-4 py-3 min-w-[190px]">
                  {{ c.name }}
                  <span class="block normal-case text-[11px] font-normal text-gray-400 mt-0.5">
                    <template v-if="c.slug === 'popularitas'">
                      bobot {{ c.weight }}% · dihitung otomatis dari frekuensi pemesanan
                    </template>
                    <template v-else>
                      bobot {{ c.weight }}% · rata-rata kuesioner pelanggan
                    </template>
                  </span>
                </th>
                <th class="px-4 py-3 text-center">Lengkap</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="menu in filteredMenus" :key="menu.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 sticky left-0 bg-white z-10">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-amber-100 flex items-center justify-center text-lg flex-shrink-0">
                      <img v-if="menu.image_path" :src="asset(menu.image_path)" :alt="menu.name" class="w-full h-full object-cover" />
                      <span v-else>🍜</span>
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-gray-800 truncate max-w-[160px]">{{ menu.name }}</p>
                      <p class="text-gray-400 text-xs capitalize">{{ menu.category }}</p>
                    </div>
                  </div>
                </td>
                <td v-for="c in criteria" :key="c.id" class="px-4 py-3">
                  <template v-if="c.slug === 'popularitas'">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-xs font-medium text-amber-700">
                      {{ popularity[menu.id]?.score ?? 1 }} · {{ c.scale_labels[(popularity[menu.id]?.score ?? 1) - 1] }}
                    </span>
                    <p class="text-[10px] text-gray-400 mt-1">
                      Dipesan {{ popularity[menu.id]?.order_count ?? 0 }}x · dihitung otomatis
                    </p>
                  </template>
                  <template v-else-if="ratings[menu.id]?.[c.id]">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-xs font-medium text-amber-700">
                      {{ scoreFor(menu.id, c) }} · {{ c.scale_labels[scoreFor(menu.id, c) - 1] }}
                    </span>
                    <p class="text-[10px] text-gray-400 mt-1">
                      Rata-rata {{ Number(ratings[menu.id][c.id].average).toFixed(1) }} dari {{ ratings[menu.id][c.id].count }} penilaian
                    </p>
                  </template>
                  <span v-else class="text-xs text-gray-300 italic">Belum ada data pelanggan</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span v-if="isComplete(menu.id)" class="text-green-600 text-lg" title="Skor lengkap di semua kriteria">✓</span>
                  <span v-else class="text-xs text-gray-400" :title="`${completedCount(menu.id)} dari ${criteria.length} kriteria terhitung`">
                    {{ completedCount(menu.id) }}/{{ criteria.length }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredMenus.length === 0">
                <td :colspan="criteria.length + 2" class="px-5 py-10 text-center text-gray-400">Tidak ada menu yang cocok.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { asset } from '@/utils/asset'

const props = defineProps({
  criteria: { type: Array, default: () => [] },
  menus: { type: Array, default: () => [] },
  ratings: { type: Object, default: () => ({}) },
  popularity: { type: Object, default: () => ({}) },
})

const search = ref('')
const categoryFilter = ref('')

const categories = computed(() => [...new Set(props.menus.map((m) => m.category))])

function scoreFor(menuId, criterion) {
  if (criterion.slug === 'popularitas') {
    return props.popularity[menuId]?.score ?? null
  }

  const summary = props.ratings[menuId]?.[criterion.id]
  if (!summary) return null

  return Math.min(5, Math.max(1, Math.round(Number(summary.average))))
}

function completedCount(menuId) {
  return props.criteria.filter((c) => scoreFor(menuId, c) !== null).length
}

function isComplete(menuId) {
  return completedCount(menuId) === props.criteria.length
}

const completeCount = computed(() => props.menus.filter((m) => isComplete(m.id)).length)

const filteredMenus = computed(() =>
  props.menus.filter((m) => {
    const matchesSearch = m.name.toLowerCase().includes(search.value.toLowerCase())
    const matchesCategory = !categoryFilter.value || m.category === categoryFilter.value
    return matchesSearch && matchesCategory
  })
)
</script>
