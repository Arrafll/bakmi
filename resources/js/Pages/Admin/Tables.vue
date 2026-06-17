<template>
  <AdminLayout title="Kelola Meja">

    <!-- ── Flash messages ──────────────────────────────────────────────────── -->
    <div v-if="flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
      ✅ {{ flash.success }}
    </div>
    <div v-if="flash.error" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
      ❌ {{ flash.error }}
    </div>

    <!-- ── Add Table Form ──────────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
      <h2 class="text-base font-semibold text-gray-700 mb-4">Tambah Meja Baru</h2>
      <form @submit.prevent="addTable" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
          <label class="block text-xs text-gray-500 mb-1">Nama Meja *</label>
          <input
            v-model="newTable.name"
            type="text"
            placeholder="Table 1"
            required
            maxlength="100"
            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
          />
          <p v-if="addErrors.name" class="text-xs text-red-500 mt-1">{{ addErrors.name }}</p>
        </div>
        <div class="flex-1 min-w-40">
          <label class="block text-xs text-gray-500 mb-1">Cabang</label>
          <input
            v-model="newTable.branch"
            type="text"
            placeholder="main"
            maxlength="100"
            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
          />
        </div>
        <button
          type="submit"
          :disabled="adding"
          class="px-6 py-2 bg-amber-700 hover:bg-amber-600 text-white font-semibold rounded-xl
                 disabled:opacity-50 transition-colors"
        >
          {{ adding ? 'Menyimpan…' : 'Tambah' }}
        </button>
      </form>
    </div>

    <!-- ── Tables List ─────────────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-700">Daftar Meja ({{ tables.length }})</h2>
        <span class="text-xs text-gray-400">QR token panjang 40 karakter – tidak dapat ditebak</span>
      </div>

      <div v-if="!tables.length" class="text-center text-gray-400 py-16">
        <p class="text-4xl mb-2">🪑</p>
        <p>Belum ada meja. Tambahkan meja di atas.</p>
      </div>

      <div class="overflow-x-auto">
        <table v-if="tables.length" class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">Nama</th>
              <th class="px-5 py-3">Cabang</th>
              <th class="px-5 py-3">Status</th>
              <th class="px-5 py-3">QR Code</th>
              <th class="px-5 py-3">Total Pesanan</th>
              <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="table in tables" :key="table.id" class="hover:bg-gray-50 transition-colors">

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

              <td class="px-5 py-3 text-gray-500">{{ table.branch }}</td>

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
    </div>

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
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  tables: { type: Array, default: () => [] },
})

const page  = usePage()
const flash = computed(() => page.props.flash ?? {})

// ── Add form ──────────────────────────────────────────────────────────────────
const newTable  = ref({ name: '', branch: 'main' })
const addErrors = ref({})
const adding    = ref(false)

function addTable() {
  addErrors.value = {}
  adding.value = true
  router.post(route('admin.tables.store'), newTable.value, {
    preserveScroll: true,
    onSuccess: () => { newTable.value = { name: '', branch: 'main' } },
    onError: (e) => { addErrors.value = e },
    onFinish: () => { adding.value = false },
  })
}

// ── Inline edit ───────────────────────────────────────────────────────────────
const editingId = ref(null)
const editForm  = ref({ name: '', branch: '', is_active: true })

function startEdit(table) {
  editingId.value = table.id
  editForm.value  = { name: table.name, branch: table.branch, is_active: table.is_active }
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
