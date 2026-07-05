<template>
  <AdminLayout title="Riwayat Penilaian Pelanggan">
    <!-- Computed summary: average customer score per menu, per criterion -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-700">Skor Terhitung per Kriteria</h2>
        <p class="text-xs text-gray-400 mt-0.5">Dihitung otomatis dari seluruh kuesioner yang masuk untuk tiap menu.</p>
      </div>

      <div v-if="summaryRows.length === 0" class="px-6 py-10 text-center text-gray-400">
        Belum ada kuesioner yang masuk.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">Menu</th>
              <th v-for="c in criteria" :key="c.id" class="px-4 py-3">{{ c.name }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="row in summaryRows" :key="row.menu_id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3 font-medium text-gray-800">{{ row.name }}</td>
              <td v-for="c in criteria" :key="c.id" class="px-4 py-3 text-gray-600">
                <template v-if="row.scores[c.id]">
                  <span class="font-semibold text-amber-700">{{ Number(row.scores[c.id].average).toFixed(1) }}</span>
                  <span class="text-gray-400 text-xs"> ({{ row.scores[c.id].count }})</span>
                </template>
                <span v-else class="text-gray-300">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Raw submission log -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-700">Riwayat Kuesioner</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ submissions.total }} kuesioner tercatat. Klik baris untuk melihat rincian jawaban.</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">Menu</th>
              <th class="px-5 py-3">Pelanggan</th>
              <th class="px-5 py-3">No. Pesanan</th>
              <th class="px-5 py-3">Waktu</th>
              <th class="px-5 py-3 text-center">Jumlah Jawaban</th>
              <th class="px-5 py-3 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-for="submission in submissions.data" :key="submission.id">
              <tr class="hover:bg-gray-50 transition-colors cursor-pointer" @click="toggleExpand(submission.id)">
                <td class="px-5 py-3 font-medium text-gray-800">{{ submission.menu?.name ?? '—' }}</td>
                <td class="px-5 py-3 text-gray-600">{{ submission.order?.customer_name ?? '—' }}</td>
                <td class="px-5 py-3 font-mono text-gray-500">#{{ String(submission.order?.id ?? 0).padStart(5, '0') }}</td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ formatDate(submission.created_at) }}</td>
                <td class="px-5 py-3 text-center text-gray-600">{{ submission.scores.length }}</td>
                <td class="px-5 py-3 text-gray-400 text-center">{{ expanded.has(submission.id) ? '▲' : '▼' }}</td>
              </tr>
              <tr v-if="expanded.has(submission.id)">
                <td :colspan="6" class="bg-amber-50/50 px-5 py-4">
                  <div v-if="submission.scores.length === 0" class="text-xs text-gray-400">Belum ada jawaban untuk kuesioner ini.</div>
                  <div v-else class="flex flex-wrap gap-2">
                    <span
                      v-for="score in submission.scores" :key="score.id"
                      class="px-3 py-1.5 rounded-lg bg-white border border-amber-200 text-xs"
                    >
                      <span class="font-medium text-gray-700">{{ score.criterion?.name ?? '—' }}:</span>
                      <span class="text-amber-700 font-semibold">{{ score.score }}/5</span>
                    </span>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="submissions.data.length === 0">
              <td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada kuesioner yang masuk.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="submissions.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-sm text-gray-500">
          Menampilkan {{ submissions.from }} - {{ submissions.to }} dari {{ submissions.total }} kuesioner
        </p>
        <div class="flex gap-2">
          <button
            @click="goToPage(submissions.current_page - 1)"
            :disabled="submissions.current_page === 1"
            class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
          >
            Sebelumnya
          </button>
          <button
            v-for="page in visiblePages" :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
              page === submissions.current_page
                ? 'bg-amber-700 text-white'
                : 'border border-gray-300 hover:bg-gray-50',
              page === '...' ? 'cursor-default border-none hover:bg-transparent' : ''
            ]"
            :disabled="page === '...'"
          >
            {{ page }}
          </button>
          <button
            @click="goToPage(submissions.current_page + 1)"
            :disabled="submissions.current_page === submissions.last_page"
            class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  criteria: { type: Array, default: () => [] },
  submissions: {
    type: Object,
    default: () => ({ data: [], total: 0, current_page: 1, last_page: 1, per_page: 10, from: 0, to: 0 }),
  },
  summary: { type: Object, default: () => ({}) },
  menuNames: { type: Object, default: () => ({}) },
})

const expanded = ref(new Set())

function toggleExpand(id) {
  const next = new Set(expanded.value)
  if (next.has(id)) {
    next.delete(id)
  } else {
    next.add(id)
  }
  expanded.value = next
}

const summaryRows = computed(() =>
  Object.entries(props.summary).map(([menuId, scores]) => ({
    menu_id: Number(menuId),
    name: props.menuNames[menuId] ?? `Menu #${menuId}`,
    scores,
  }))
)

function formatDate(date) {
  if (!date) return '-'
  const d = new Date(date)
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(d)
}

const visiblePages = computed(() => {
  const total = props.submissions.last_page
  const current = props.submissions.current_page
  const pages = []

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    pages.push(1)
    if (current > 3) pages.push('...')

    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)

    for (let i = start; i <= end; i++) pages.push(i)

    if (current < total - 2) pages.push('...')
    pages.push(total)
  }

  return pages
})

function goToPage(page) {
  if (page >= 1 && page <= props.submissions.last_page) {
    router.get(route('admin.review-submissions.index'), { page }, { preserveState: true })
  }
}
</script>
