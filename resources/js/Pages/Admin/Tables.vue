<template>
  <AdminLayout title="Kelola Meja">
    <!-- Header bar -->
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-gray-500">{{ tables.total }} item meja</p>
      <button @click="openCreate" class="bg-amber-700 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        + Tambah Meja
      </button>
    </div>

    <!-- ── Flash messages ──────────────────────────────────────────────────── -->
    <div v-if="flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
      ✅ {{ flash.success }}
    </div>
    <div v-if="flash.error" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
      ❌ {{ flash.error }}
    </div>

    <!-- ── Tables List ─────────────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-700">Daftar Meja ({{ tables.total }})</h2>
      </div>

      <div v-if="!tables.data.length" class="text-center text-gray-400 py-16">
        <BuildingStorefrontIcon class="w-12 h-12 mx-auto mb-4" />
        <p>Belum ada data meja yang didaftarkan</p>
      </div>

      <div class="overflow-x-auto">
        <table v-if="tables.data.length" class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">Nama</th>
              <th class="px-5 py-3">Status</th>
              <th class="px-5 py-3">QR Code</th>
              <th class="px-5 py-3">Total Pesanan</th>
              <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="table in tables.data" :key="table.id" class="hover:bg-gray-50 transition-colors">

              <!-- Name (editable inline) -->
              <td class="px-5 py-3">
                <span
                  v-if="editingId !== table.id"
                  class="font-medium text-gray-800 cursor-pointer hover:underline"
                  @click="startEdit(table)"
                >
                  {{ table.name }}
                </span>
                <input
                  v-else
                  v-model="editForm.name"
                  type="text"
                  maxlength="100"
                  class="border border-amber-300 rounded-lg px-2 py-1 text-sm w-36 focus:outline-none focus:ring-2 focus:ring-amber-400"
                  @keyup.enter="saveEdit(table)"
                  @keyup.escape="editingId = null"
                />
              </td>

              <!-- Active toggle -->
              <td class="px-5 py-3">
                <button
                  @click="toggleActive(table)"
                  :class="table.is_active
                    ? 'bg-green-100 text-green-700'
                    : 'bg-gray-100 text-gray-500'"
                  class="px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors hover:opacity-80"
                >
                  {{ table.is_active ? 'Aktif' : 'Nonaktif' }}
                </button>
              </td>

              <!-- QR preview + download -->
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <img
                    v-if="table.qr_code_url"
                    :src="table.qr_code_url"
                    :alt="`QR ${table.name}`"
                    class="w-12 h-12 border border-gray-200 rounded-lg p-0.5"
                  />
                  <span v-else class="text-gray-400 text-xs">Belum digenerate</span>

                  <a
                    v-if="table.qr_code_url"
                    :href="route('admin.tables.download-qr', table.id)"
                    class="text-xs text-amber-700 hover:underline font-medium"
                    target="_blank"
                    rel="noopener"
                  >
                    Download
                  </a>
                </div>
              </td>

              <td class="px-5 py-3 text-gray-500">{{ table.orders_count }}</td>

              <!-- Actions -->
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2 flex-wrap">
                  <!-- Save inline edit -->
                  <button
                    v-if="editingId === table.id"
                    @click="saveEdit(table)"
                    class="text-xs px-3 py-1 bg-amber-700 text-white rounded-lg hover:bg-amber-600 transition-colors"
                  >
                    Simpan
                  </button>
                  <button
                    v-if="editingId === table.id"
                    @click="editingId = null"
                    class="text-xs px-3 py-1 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors"
                  >
                    Batal
                  </button>

                  <!-- Regenerate QR -->
                  <button
                    v-if="editingId !== table.id"
                    @click="confirmRegenerate(table)"
                    class="text-xs px-3 py-1 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors"
                  >
                    Regenerasi QR
                  </button>

                  <!-- Delete -->
                  <button
                    v-if="editingId !== table.id"
                    @click="confirmDelete(table)"
                    class="text-xs px-3 py-1 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors"
                  >
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="tables.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-sm text-gray-500">
          Menampilkan {{ tables.from }} - {{ tables.to }} dari {{ tables.total }} meja
        </p>
        <div class="flex gap-2">
          <button
            @click="goToPage(tables.current_page - 1)"
            :disabled="tables.current_page === 1"
            class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
          >
            Sebelumnya
          </button>
          <button
            v-for="page in visiblePages" :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
              page === tables.current_page
                ? 'bg-amber-700 text-white'
                : 'border border-gray-300 hover:bg-gray-50',
              page === '...' ? 'cursor-default border-none hover:bg-transparent' : ''
            ]"
            :disabled="page === '...'"
          >
            {{ page }}
          </button>
          <button
            @click="goToPage(tables.current_page + 1)"
            :disabled="tables.current_page === tables.last_page"
            class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Create -->
    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="modal.open = false" />
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Meja Baru</h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Meja <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="Contoh: Meja 1" required maxlength="100" />
              <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="modal.open = false" class="flex-1 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                Batal
              </button>
              <button type="submit" :disabled="submitting" class="flex-1 py-2 bg-amber-700 hover:bg-amber-600 disabled:opacity-60 text-white rounded-xl text-sm font-semibold transition-colors">
                {{ submitting ? 'Menyimpan...' : 'Tambah' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Confirm Dialog ──────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="confirm.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
          <h3 class="font-bold text-gray-800 mb-2">{{ confirm.title }}</h3>
          <p class="text-sm text-gray-500 mb-5">{{ confirm.message }}</p>
          <div class="flex gap-3 justify-end">
            <button
              @click="confirm.show = false"
              class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors"
            >
              Batal
            </button>
            <button
              @click="confirm.onConfirm(); confirm.show = false"
              :class="confirm.danger ? 'bg-red-600 hover:bg-red-700' : 'bg-amber-700 hover:bg-amber-600'"
              class="px-4 py-2 text-sm text-white rounded-xl transition-colors"
            >
              {{ confirm.confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { BuildingStorefrontIcon } from '@heroicons/vue/24/outline'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  tables: { type: Object, default: () => ({ data: [], total: 0, current_page: 1, last_page: 1, per_page: 10, from: 0, to: 0 }) },
})

const page  = usePage()
const flash = computed(() => page.props.flash ?? {})

const visiblePages = computed(() => {
  const total = props.tables.last_page
  const current = props.tables.current_page
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
  if (page >= 1 && page <= props.tables.last_page) {
    router.get(route('admin.tables.index'), { page: page }, { preserveState: true })
  }
}

// ── Modal form ────────────────────────────────────────────────────────────────
const form = ref({ name: '' })
const errors = computed(() => page.props.errors ?? {})
const submitting = ref(false)
const modal = ref({ open: false, isEdit: false })

function openCreate() {
  form.value = { name: '' }
  modal.value = { open: true, isEdit: false }
}

function submitForm() {
  submitting.value = true
  router.post(route('admin.tables.store'), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      submitting.value = false
      modal.value.open = false
      form.value = { name: '' }
    },
    onError: (e) => {
      submitting.value = false
    },
    onFinish: () => {
      submitting.value = false
    },
  })
}

// ── Inline edit ───────────────────────────────────────────────────────────────
const editingId = ref(null)
const editForm  = ref({ name: '', is_active: true })

function startEdit(table) {
  editingId.value = table.id
  editForm.value  = { name: table.name, is_active: table.is_active }
}

function saveEdit(table) {
  router.put(route('admin.tables.update', table.id), editForm.value, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null },
  })
}

function toggleActive(table) {
  router.put(route('admin.tables.update', table.id), {
    is_active: !table.is_active,
  }, { preserveScroll: true })
}

// ── Regenerate QR ─────────────────────────────────────────────────────────────
function confirmRegenerate(table) {
  confirm.value = {
    show: true,
    title: `Regenerasi QR – ${table.name}`,
    message: 'QR code lama akan TIDAK VALID. Semua poster/stiker yang sudah dicetak harus diganti. Lanjutkan?',
    confirmLabel: 'Ya, Regenerasi',
    danger: false,
    onConfirm: () => {
      router.post(route('admin.tables.regenerate-qr', table.id), {}, { preserveScroll: true })
    },
  }
}

// ── Delete ────────────────────────────────────────────────────────────────────
function confirmDelete(table) {
  confirm.value = {
    show: true,
    title: `Hapus ${table.name}?`,
    message: `Semua data pesanan yang terhubung dengan meja ini akan terputus (table_id = null). QR code akan dihapus.`,
    confirmLabel: 'Hapus',
    danger: true,
    onConfirm: () => {
      router.delete(route('admin.tables.destroy', table.id), { preserveScroll: true })
    },
  }
}

// ── Confirm dialog state ──────────────────────────────────────────────────────
const confirm = ref({
  show: false, title: '', message: '',
  confirmLabel: 'OK', danger: false,
  onConfirm: () => {},
})
</script>
