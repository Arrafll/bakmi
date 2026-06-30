<template>
  <AdminLayout title="Menu Unggulan">
    <!-- Flash messages -->
    <div v-if="flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
      {{ flash.success }}
    </div>
    <div v-if="flash.error" class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
      {{ flash.error }}
    </div>
    <div v-if="errors.weights" class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
      {{ errors.weights }}
    </div>

    <!-- Tab navigation -->
    <div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-xl w-full overflow-x-auto">
      <button
        v-for="tab in tabs" :key="tab.key"
        @click="activeTab = tab.key"
        :class="[
          'flex-1 min-w-max px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap',
          activeTab === tab.key
            ? 'bg-white text-amber-800 shadow-sm'
            : 'text-gray-500 hover:text-gray-700',
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- ── Tab 1: Faktor & Bobot ───────────────────────────────────────── -->
    <div v-show="activeTab === 'criteria'">
      <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold text-gray-800">Faktor Penilaian</h2>
          <span class="text-xs text-gray-500">Total prioritas harus = 100%</span>
        </div>

        <form @submit.prevent="saveWeights">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                  <th class="px-4 py-3">Faktor</th>
                  <th class="px-4 py-3">Keterangan</th>
                  <th class="px-4 py-3">Tipe</th>
                  <th class="px-4 py-3 w-40">Bobot (%)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="c in weightForm" :key="c.key" class="hover:bg-gray-50">
                  <td class="px-4 py-3 font-medium text-gray-800">{{ c.name }}</td>
                  <td class="px-4 py-3 text-gray-500 text-xs">{{ c.description }}</td>
                  <td class="px-4 py-3">
                    <span :class="[
                      'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                      c.type === 'benefit' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700',
                    ]">
                      {{ c.type === 'benefit' ? 'Lebih besar lebih baik' : 'Lebih kecil lebih baik' }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <input
                        v-model.number="c.weightPct"
                        type="number" min="0" max="100" step="1"
                        class="w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-amber-500"
                      />
                      <span class="text-gray-400 text-xs">%</span>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="border-t-2 border-gray-200">
                  <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Total</td>
                  <td class="px-4 py-3">
                    <span :class="['text-sm font-bold', Math.abs(totalWeightPct - 100) < 0.1 ? 'text-green-600' : 'text-red-600']">
                      {{ totalWeightPct.toFixed(0) }}%
                    </span>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mt-5 flex items-center gap-3">
            <button
              type="submit"
              :disabled="Math.abs(totalWeightPct - 100) >= 0.1 || savingWeights"
              class="bg-amber-700 hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors"
            >
              {{ savingWeights ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <span v-if="Math.abs(totalWeightPct - 100) >= 0.1" class="text-xs text-red-500">
              Total harus 100% (sekarang {{ totalWeightPct.toFixed(0) }}%)
            </span>
          </div>
        </form>
      </div>
    </div>

    <!-- ── Tab 2: Nilai Menu ─────────────────────────────────────────────── -->
    <div v-show="activeTab === 'scores'">
      <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold text-gray-800">Penilaian Menu</h2>
          <p class="text-xs text-gray-500">Skala 1–10 (Harga &amp; Popularitas otomatis)</p>
        </div>

        <form @submit.prevent="saveScores">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                  <th class="px-3 py-3 min-w-40">Menu</th>
                  <th class="px-3 py-3 text-center">Harga</th>
                  <th class="px-3 py-3 text-center">Rasa</th>
                  <th class="px-3 py-3 text-center">Popularitas</th>
                  <th class="px-3 py-3 text-center">Porsi</th>
                  <th class="px-3 py-3 text-center">Waktu Saji</th>
                  <th class="px-3 py-3 text-center">Tampilan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="menu in menus" :key="menu.id" class="hover:bg-gray-50">
                  <td class="px-3 py-3 font-medium text-gray-800">
                    <div>{{ menu.name }}</div>
                    <div class="text-xs text-gray-400 capitalize">{{ menu.category }}</div>
                  </td>
                  <td class="px-3 py-3 text-center text-gray-500">
                    {{ formatPrice(menu.price) }}
                  </td>
                  <td class="px-3 py-3 text-center">
                    <input v-model.number="scoreForm[menu.id].taste"
                      type="number" min="1" max="10" step="1"
                      class="w-14 border border-gray-300 rounded-lg px-1 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-amber-500" />
                  </td>
                  <td class="px-3 py-3 text-center text-gray-500">
                    {{ popularity[menu.id] ?? 0 }} pcs
                  </td>
                  <td class="px-3 py-3 text-center">
                    <input v-model.number="scoreForm[menu.id].portion"
                      type="number" min="1" max="10" step="1"
                      class="w-14 border border-gray-300 rounded-lg px-1 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-amber-500" />
                  </td>
                  <td class="px-3 py-3 text-center">
                    <input v-model.number="scoreForm[menu.id].preparation_time"
                      type="number" min="1" max="10" step="1"
                      class="w-14 border border-gray-300 rounded-lg px-1 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-amber-500" />
                  </td>
                  <td class="px-3 py-3 text-center">
                    <input v-model.number="scoreForm[menu.id].presentation"
                      type="number" min="1" max="10" step="1"
                      class="w-14 border border-gray-300 rounded-lg px-1 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-amber-500" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-5">
            <button type="submit" :disabled="savingScores"
              class="bg-amber-700 hover:bg-amber-600 disabled:opacity-50 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors">
              {{ savingScores ? 'Menyimpan...' : 'Simpan Semua Nilai' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── Tab 3: Detail Analisis ──────────────────────────────────────── -->
    <div v-show="activeTab === 'process'">
      <div v-if="result.ranking.length === 0" class="bg-white rounded-2xl shadow-sm p-8 text-center text-gray-400">
        Belum ada data untuk ditampilkan.
      </div>

      <template v-else>
        <!-- Data Penilaian -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
          <h3 class="font-semibold text-gray-800 mb-1">Data Penilaian</h3>
          <p class="text-xs text-gray-500 mb-4">Nilai tiap menu berdasarkan masing-masing faktor.</p>
          <div class="overflow-x-auto">
            <table class="w-full text-xs border border-gray-200 rounded-lg">
              <thead class="bg-amber-50">
                <tr>
                  <th class="border border-gray-200 px-3 py-2 text-left text-gray-600">Menu</th>
                  <th v-for="c in criteria" :key="c.key" class="border border-gray-200 px-3 py-2 text-center text-gray-600">
                    {{ c.name }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(r, i) in result.ranking" :key="r.menu.id" :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                  <td class="border border-gray-200 px-3 py-2 font-medium">{{ r.menu.name }}</td>
                  <td v-for="c in criteria" :key="c.key" class="border border-gray-200 px-3 py-2 text-center">
                    <span v-if="c.key === 'price'">{{ formatPrice(r.matrix[c.key]) }}</span>
                    <span v-else-if="c.key === 'popularity'">{{ r.matrix[c.key] }} pcs</span>
                    <span v-else>{{ r.matrix[c.key] }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Perbandingan Nilai -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
          <h3 class="font-semibold text-gray-800 mb-1">Perbandingan Nilai</h3>
          <p class="text-xs text-gray-400 mb-4">Nilai tiap menu dibandingkan dalam skala 0–1 terhadap menu terbaik di setiap faktor.</p>
          <div class="overflow-x-auto">
            <table class="w-full text-xs border border-gray-200 rounded-lg">
              <thead class="bg-amber-50">
                <tr>
                  <th class="border border-gray-200 px-3 py-2 text-left text-gray-600">Menu</th>
                  <th v-for="c in criteria" :key="c.key" class="border border-gray-200 px-3 py-2 text-center text-gray-600">
                    {{ c.name }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(r, i) in result.ranking" :key="r.menu.id" :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                  <td class="border border-gray-200 px-3 py-2 font-medium">{{ r.menu.name }}</td>
                  <td v-for="c in criteria" :key="c.key" class="border border-gray-200 px-3 py-2 text-center">
                    {{ r.normalized[c.key]?.toFixed(4) ?? '0.0000' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Skor Akhir -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
          <h3 class="font-semibold text-gray-800 mb-4">Skor Akhir</h3>

          <!-- Weights row -->
          <div class="flex flex-wrap gap-2 mb-4">
            <div v-for="c in criteria" :key="c.key"
              class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-1.5 text-xs">
              <span class="font-medium text-amber-800">{{ c.name }}</span>
              <span class="text-amber-600 ml-1">{{ (c.weight * 100).toFixed(0) }}%</span>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-xs border border-gray-200 rounded-lg">
              <thead class="bg-amber-50">
                <tr>
                  <th class="border border-gray-200 px-3 py-2 text-left text-gray-600">Menu</th>
                  <th v-for="c in criteria" :key="c.key" class="border border-gray-200 px-3 py-2 text-center text-gray-600">
                    {{ (c.weight * 100).toFixed(0) }}%
                  </th>
                  <th class="border border-gray-200 px-3 py-2 text-center text-amber-700 font-bold">Skor</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(r, i) in result.ranking" :key="r.menu.id" :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                  <td class="border border-gray-200 px-3 py-2 font-medium">{{ r.menu.name }}</td>
                  <td v-for="c in criteria" :key="c.key" class="border border-gray-200 px-3 py-2 text-center text-gray-500">
                    {{ (c.weight * (r.normalized[c.key] ?? 0)).toFixed(4) }}
                  </td>
                  <td class="border border-gray-200 px-3 py-2 text-center font-bold text-amber-700">
                    {{ r.score.toFixed(4) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Peringkat -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <h3 class="font-semibold text-gray-800 mb-4">Peringkat Menu</h3>
          <div class="space-y-3">
            <div v-for="r in result.ranking" :key="r.menu.id"
              class="flex items-center gap-4 p-3 rounded-xl border"
              :class="r.rank === 1 ? 'border-amber-300 bg-amber-50' : 'border-gray-100 bg-gray-50'"
            >
              <div :class="[
                'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0',
                r.rank === 1 ? 'bg-amber-500 text-white' :
                r.rank === 2 ? 'bg-gray-400 text-white' :
                r.rank === 3 ? 'bg-orange-400 text-white' : 'bg-gray-200 text-gray-600',
              ]">{{ r.rank }}</div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm">{{ r.menu.name }}</p>
                <p class="text-xs text-gray-400 capitalize">{{ r.menu.category }}</p>
              </div>
              <div class="text-right">
                <p class="text-amber-700 font-bold text-base">{{ r.score.toFixed(4) }}</p>
                <p class="text-xs text-gray-400">skor</p>
              </div>
              <div class="w-24 bg-gray-200 rounded-full h-2 hidden sm:block">
                <div class="bg-amber-500 h-2 rounded-full" :style="{ width: (r.score * 100).toFixed(1) + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- ── Tab 4: Hasil & Laporan ───────────────────────────────────────── -->
    <div v-show="activeTab === 'report'">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 5 -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-4">Menu Terpopuler</h2>
          <div v-if="result.ranking.length === 0" class="text-center py-8 text-gray-400 text-sm">Belum ada data.</div>
          <div v-else class="space-y-3">
            <div v-for="r in result.ranking.slice(0, 5)" :key="r.menu.id"
              class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
              <div :class="[
                'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0',
                r.rank === 1 ? 'bg-amber-500 text-white' :
                r.rank === 2 ? 'bg-gray-400 text-white' :
                r.rank === 3 ? 'bg-orange-400 text-white' : 'bg-gray-200 text-gray-600',
              ]">{{ r.rank }}</div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-800 text-sm truncate">{{ r.menu.name }}</p>
                <p class="text-xs text-gray-400">{{ formatPrice(r.menu.price) }}</p>
              </div>
              <div class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1">
                {{ r.score.toFixed(4) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Sales report -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-4">Laporan Penjualan</h2>
          <div v-if="Object.keys(popularity).length === 0" class="text-center py-8 text-gray-400 text-sm">Belum ada data penjualan.</div>
          <div v-else class="space-y-3">
            <div v-for="menu in popularityRanked" :key="menu.id" class="flex items-center gap-3">
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-sm font-medium text-gray-700 truncate">{{ menu.name }}</span>
                  <span class="text-xs text-gray-500 ml-2 shrink-0">{{ menu.total }} pcs</span>
                </div>
                <div class="bg-gray-100 rounded-full h-1.5">
                  <div class="bg-amber-500 h-1.5 rounded-full"
                    :style="{ width: maxPopularity > 0 ? ((menu.total / maxPopularity) * 100) + '%' : '0%' }">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  criteria:  { type: Array, default: () => [] },
  menus:     { type: Array, default: () => [] },
  scoresMap: { type: Object, default: () => ({}) },
  popularity:{ type: Object, default: () => ({}) },
  result: {
    type: Object,
    default: () => ({ matrix: {}, normalized: {}, preferences: {}, ranking: [] }),
  },
})

const page   = usePage()
const flash  = computed(() => page.props.flash ?? {})
const errors = computed(() => page.props.errors ?? {})

const tabs = [
  { key: 'criteria', label: 'Faktor & Bobot' },
  { key: 'scores',   label: 'Nilai Menu' },
  { key: 'process',  label: 'Detail Analisis' },
  { key: 'report',   label: 'Hasil & Laporan' },
]
const activeTab = ref('criteria')

// ── Weight form ────────────────────────────────────────────────────────────
const weightForm = ref(
  props.criteria.map(c => ({
    key:         c.key,
    name:        c.name,
    description: c.description,
    type:        c.type,
    weightPct:   Math.round(c.weight * 100),
  }))
)

const totalWeightPct = computed(() =>
  weightForm.value.reduce((s, c) => s + (c.weightPct || 0), 0)
)

const savingWeights = ref(false)
function saveWeights() {
  savingWeights.value = true
  const weights = {}
  weightForm.value.forEach(c => {
    weights[c.key] = c.weightPct / 100
  })
  router.put(route('admin.spk.weights.update'), { weights }, {
    onFinish: () => { savingWeights.value = false },
  })
}

// ── Score form ─────────────────────────────────────────────────────────────
const scoreForm = ref(
  Object.fromEntries(
    props.menus.map(menu => [
      menu.id,
      {
        taste:            props.scoresMap[menu.id]?.taste            ?? 5,
        portion:          props.scoresMap[menu.id]?.portion          ?? 5,
        preparation_time: props.scoresMap[menu.id]?.preparation_time ?? 5,
        presentation:     props.scoresMap[menu.id]?.presentation     ?? 5,
      },
    ])
  )
)

const savingScores = ref(false)
function saveScores() {
  savingScores.value = true
  router.put(route('admin.spk.scores.update'), { scores: scoreForm.value }, {
    onFinish: () => { savingScores.value = false },
  })
}

// ── Popularity ranking ─────────────────────────────────────────────────────
const popularityRanked = computed(() => {
  return props.menus
    .map(m => ({ id: m.id, name: m.name, total: props.popularity[m.id] ?? 0 }))
    .sort((a, b) => b.total - a.total)
})

const maxPopularity = computed(() =>
  Math.max(...popularityRanked.value.map(m => m.total), 1)
)

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}
</script>
