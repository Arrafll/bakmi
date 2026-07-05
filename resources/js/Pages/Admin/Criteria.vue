<template>
  <AdminLayout title="Kriteria Penilaian">
    <!-- Header bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <div class="flex items-center gap-3">
        <p class="text-sm text-gray-500">{{ criteria.length }} kriteria terdaftar</p>
        <span
          class="text-xs font-semibold px-3 py-1 rounded-full"
          :class="totalActiveWeight === 100 ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
        >
          Total bobot aktif: {{ totalActiveWeight }}%
        </span>
      </div>
      <button @click="openCreate" class="bg-amber-700 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors flex items-center gap-2">
        <span class="text-base leading-none">+</span> Tambah Kriteria
      </button>
    </div>

    <p v-if="totalActiveWeight !== 100" class="mb-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
      Total bobot kriteria aktif sebaiknya berjumlah 100% agar skor akhir mudah dibaca sebagai persentase. Saat ini totalnya {{ totalActiveWeight }}%.
    </p>

    <!-- Flash -->
    <div v-if="flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">{{ flash.success }}</div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">Kriteria</th>
              <th class="px-5 py-3">Arah Penilaian</th>
              <th class="px-5 py-3">Bobot</th>
              <th class="px-5 py-3">Skala Nilai (1 - 5)</th>
              <th class="px-5 py-3">Status</th>
              <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="c in criteria" :key="c.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3 font-medium text-gray-800">{{ c.name }}</td>
              <td class="px-5 py-3">
                <span
                  class="px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="c.direction === 'benefit' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'"
                >
                  {{ c.direction === 'benefit' ? 'Makin tinggi makin baik' : 'Makin rendah makin baik' }}
                </span>
              </td>
              <td class="px-5 py-3 font-semibold text-gray-700">{{ c.weight }}%</td>
              <td class="px-5 py-3">
                <div class="flex flex-wrap gap-1 max-w-sm">
                  <span
                    v-for="(label, i) in c.scale_labels" :key="i"
                    class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-[11px]"
                    :title="label"
                  >
                    {{ i + 1 }}: {{ label }}
                  </span>
                </div>
              </td>
              <td class="px-5 py-3">
                <span :class="c.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded-full text-xs font-medium">
                  {{ c.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-5 py-3 text-right space-x-2">
                <button @click="openEdit(c)" class="py-2 px-3 border border-blue-300 text-blue-700 bg-blue-50 rounded-md text-sm font-medium hover:bg-blue-100 hover:border-blue-400 active:bg-blue-200 transition focus:outline-none focus:ring-2 focus:ring-blue-300">
                  Edit
                </button>
                <button @click="confirmDelete(c)" class="py-2 px-3 border border-red-300 text-red-600 bg-white rounded-md text-sm font-medium hover:bg-red-50 hover:border-red-400 active:bg-red-100 transition focus:outline-none focus:ring-2 focus:ring-red-300">
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="criteria.length === 0">
              <td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada kriteria. Klik "Tambah Kriteria" untuk mulai.</td>
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
            <h3 class="text-lg font-semibold text-gray-800">{{ modal.isEdit ? 'Edit Kriteria' : 'Tambah Kriteria Baru' }}</h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kriteria <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="Contoh: Harga" required />
              <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Arah Penilaian <span class="text-red-500">*</span></label>
                <select v-model="form.direction" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" required>
                  <option value="benefit">Makin tinggi makin baik</option>
                  <option value="cost">Makin rendah makin baik</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bobot (%) <span class="text-red-500">*</span></label>
                <input v-model.number="form.weight" type="number" min="1" max="100" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" required />
                <p v-if="errors.weight" class="text-red-500 text-xs mt-1">{{ errors.weight }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Label Skala Nilai (1 - 5)
                <span class="block text-xs font-normal text-gray-400 mt-0.5">Nilai 1 = kondisi terendah, Nilai 5 = kondisi tertinggi pada kriteria ini.</span>
              </label>
              <div class="space-y-2">
                <div v-for="i in 5" :key="i" class="flex items-center gap-2">
                  <span class="w-6 h-6 flex-shrink-0 flex items-center justify-center rounded-full bg-amber-100 text-amber-700 text-xs font-bold">{{ i }}</span>
                  <input
                    v-model="form.scale_labels[i - 1]"
                    type="text"
                    class="flex-1 border border-gray-300 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition"
                    :placeholder="`Label untuk nilai ${i}`"
                    required
                  />
                </div>
              </div>
              <p v-if="errors['scale_labels']" class="text-red-500 text-xs mt-1">{{ errors['scale_labels'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Urutan Tampil</label>
              <input v-model.number="form.sort_order" type="number" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" />
            </div>

            <div class="flex items-center gap-3">
              <input v-model="form.is_active" id="c_active" type="checkbox" class="w-4 h-4 accent-amber-600" />
              <label for="c_active" class="text-sm font-medium text-gray-700">Kriteria aktif dipakai dalam perhitungan</label>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="modal.open = false" class="flex-1 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                Batal
              </button>
              <button type="submit" :disabled="submitting" class="flex-1 py-2 bg-amber-700 hover:bg-amber-600 disabled:opacity-60 text-white rounded-xl text-sm font-semibold transition-colors">
                {{ submitting ? 'Menyimpan...' : (modal.isEdit ? 'Simpan Perubahan' : 'Tambah Kriteria') }}
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
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Hapus Kriteria?</h3>
          <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus kriteria <strong>{{ deleteTarget.name }}</strong>? Semua penilaian menu untuk kriteria ini akan ikut terhapus.</p>
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

const props = defineProps({
  criteria: { type: Array, default: () => [] },
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const errors = computed(() => page.props.errors ?? {})
const submitting = ref(false)
const deleteTarget = ref(null)

const totalActiveWeight = computed(() =>
  props.criteria.filter((c) => c.is_active).reduce((sum, c) => sum + Number(c.weight), 0)
)

const emptyForm = () => ({
  name: '',
  direction: 'benefit',
  weight: 10,
  scale_labels: ['', '', '', '', ''],
  sort_order: props.criteria.length,
  is_active: true,
})

const form = ref(emptyForm())
const modal = ref({ open: false, isEdit: false, editId: null })

function openCreate() {
  form.value = emptyForm()
  modal.value = { open: true, isEdit: false, editId: null }
}

function openEdit(c) {
  form.value = {
    name: c.name,
    direction: c.direction,
    weight: c.weight,
    scale_labels: [...c.scale_labels],
    sort_order: c.sort_order,
    is_active: !!c.is_active,
  }
  modal.value = { open: true, isEdit: true, editId: c.id }
}

function submitForm() {
  submitting.value = true

  if (modal.value.isEdit) {
    router.put(route('admin.criteria.update', modal.value.editId), form.value, {
      onFinish: () => { submitting.value = false; modal.value.open = false },
    })
  } else {
    router.post(route('admin.criteria.store'), form.value, {
      onFinish: () => { submitting.value = false; modal.value.open = false },
    })
  }
}

function confirmDelete(c) {
  deleteTarget.value = c
}

function doDelete() {
  submitting.value = true
  router.delete(route('admin.criteria.destroy', deleteTarget.value.id), {
    onFinish: () => { submitting.value = false; deleteTarget.value = null },
  })
}
</script>
