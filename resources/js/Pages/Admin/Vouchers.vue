<template>
  <AdminLayout title="Voucher Promo">
    <!-- Header bar -->
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-gray-500">{{ vouchers.length }} voucher terdaftar</p>
      <button @click="openCreate" class="bg-amber-700 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors flex items-center gap-2">
        <span class="text-base leading-none">+</span> Buat Voucher
      </button>
    </div>

    <!-- Flash -->
    <div v-if="flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">{{ flash.success }}</div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">Kode</th>
              <th class="px-5 py-3">Deskripsi</th>
              <th class="px-5 py-3">Diskon</th>
              <th class="px-5 py-3">Min. Order</th>
              <th class="px-5 py-3">Penggunaan</th>
              <th class="px-5 py-3">Masa Berlaku</th>
              <th class="px-5 py-3">Status</th>
              <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="v in vouchers" :key="v.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3">
                <span class="font-mono font-bold text-amber-700 tracking-wider">{{ v.code }}</span>
              </td>
              <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ v.description ?? '—' }}</td>
              <td class="px-5 py-3 font-medium text-gray-800">
                <span v-if="v.discount_type === 'percentage'">{{ v.discount_value }}%</span>
                <span v-else>{{ formatPrice(v.discount_value) }}</span>
              </td>
              <td class="px-5 py-3 text-gray-600">{{ v.min_order > 0 ? formatPrice(v.min_order) : '—' }}</td>
              <td class="px-5 py-3 text-gray-600">
                {{ v.used_count }} / {{ v.max_uses ?? '∞' }}
              </td>
              <td class="px-5 py-3 text-gray-500 text-xs">
                <template v-if="v.valid_from || v.valid_until">
                  {{ v.valid_from ?? '...' }} s/d {{ v.valid_until ?? '...' }}
                </template>
                <span v-else>Tidak dibatasi</span>
              </td>
              <td class="px-5 py-3">
                <span :class="v.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded-full text-xs font-medium">
                  {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-5 py-3 text-right space-x-2">
                <button @click="openEdit(v)" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">Edit</button>
                <button @click="confirmDelete(v)" class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">Hapus</button>
              </td>
            </tr>
            <tr v-if="vouchers.length === 0">
              <td colspan="8" class="px-5 py-10 text-center text-gray-400">Belum ada voucher. Klik "Buat Voucher" untuk memulai.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Create/Edit -->
    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="modal.open = false" />
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">{{ modal.isEdit ? 'Edit Voucher' : 'Buat Voucher Baru' }}</h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kode Voucher <span class="text-red-500">*</span></label>
              <input v-model="form.code" type="text" class="input uppercase" placeholder="Contoh: HEMAT20" :disabled="modal.isEdit" required />
              <p v-if="errors.code" class="text-red-500 text-xs mt-1">{{ errors.code }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
              <input v-model="form.description" type="text" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="Contoh: Diskon 20% untuk semua menu" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon <span class="text-red-500">*</span></label>
                <select v-model="form.discount_type" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" required>
                  <option value="percentage">Persentase (%)</option>
                  <option value="fixed">Nominal Tetap (Rp)</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Nilai Diskon <span class="text-red-500">*</span>
                  <span class="text-gray-400 font-normal">({{ form.discount_type === 'percentage' ? '%' : 'Rp' }})</span>
                </label>
                <input v-model.number="form.discount_value" type="number" min="0" :max="form.discount_type === 'percentage' ? 100 : undefined" step="any" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" required />
                <p v-if="errors.discount_value" class="text-red-500 text-xs mt-1">{{ errors.discount_value }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min. Total Pesanan (Rp)</label>
                <input v-model.number="form.min_order" type="number" min="0" step="500" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="0" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maks. Penggunaan</label>
                <input v-model.number="form.max_uses" type="number" min="1" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="Kosongkan = tak terbatas" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Dari</label>
                <input v-model="form.valid_from" type="date" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai</label>
                <input v-model="form.valid_until" type="date" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" />
              </div>
            </div>

            <div class="flex items-center gap-3">
              <input v-model="form.is_active" id="v_active" type="checkbox" class="w-4 h-4 accent-amber-600" />
              <label for="v_active" class="text-sm font-medium text-gray-700">Voucher aktif</label>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="modal.open = false" class="flex-1 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                Batal
              </button>
              <button type="submit" :disabled="submitting" class="flex-1 py-2 bg-amber-700 hover:bg-amber-600 disabled:opacity-60 text-white rounded-xl text-sm font-semibold transition-colors">
                {{ submitting ? 'Menyimpan...' : (modal.isEdit ? 'Simpan Perubahan' : 'Buat Voucher') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Confirm Delete -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="deleteTarget = null" />
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
          <p class="text-4xl mb-3">🗑️</p>
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Hapus Voucher?</h3>
          <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus voucher <strong>{{ deleteTarget.code }}</strong>?</p>
          <div class="flex gap-3">
            <button @click="deleteTarget = null" class="flex-1 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition-colors">Batal</button>
            <button @click="doDelete" :disabled="submitting" class="flex-1 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white rounded-xl text-sm font-semibold transition-colors">Hapus</button>
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
import { useFormat } from '@/composables/useFormat'

defineProps({
  vouchers: { type: Array, default: () => [] },
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const errors = computed(() => page.props.errors ?? {})
const { formatPrice } = useFormat()
const submitting = ref(false)
const deleteTarget = ref(null)

const emptyForm = () => ({
  code: '',
  description: '',
  discount_type: 'percentage',
  discount_value: '',
  min_order: 0,
  max_uses: null,
  valid_from: '',
  valid_until: '',
  is_active: true,
})

const form = ref(emptyForm())
const modal = ref({ open: false, isEdit: false, editId: null })

function openCreate() {
  form.value = emptyForm()
  modal.value = { open: true, isEdit: false, editId: null }
}

function openEdit(v) {
  form.value = {
    code: v.code,
    description: v.description ?? '',
    discount_type: v.discount_type,
    discount_value: v.discount_value,
    min_order: v.min_order,
    max_uses: v.max_uses,
    valid_from: v.valid_from ?? '',
    valid_until: v.valid_until ?? '',
    is_active: v.is_active,
  }
  modal.value = { open: true, isEdit: true, editId: v.id }
}

function submitForm() {
  submitting.value = true
  const payload = { ...form.value }
  payload.code = payload.code.toUpperCase()
  if (payload.max_uses === '' || payload.max_uses === null) payload.max_uses = null
  if (!payload.valid_from) payload.valid_from = null
  if (!payload.valid_until) payload.valid_until = null

  if (modal.value.isEdit) {
    router.put(route('admin.vouchers.update', modal.value.editId), payload, {
      onFinish: () => { submitting.value = false; modal.value.open = false },
    })
  } else {
    router.post(route('admin.vouchers.store'), payload, {
      onFinish: () => { submitting.value = false; modal.value.open = false },
    })
  }
}

function confirmDelete(v) {
  deleteTarget.value = v
}

function doDelete() {
  submitting.value = true
  router.delete(route('admin.vouchers.destroy', deleteTarget.value.id), {
    onFinish: () => { submitting.value = false; deleteTarget.value = null },
  })
}
</script>
