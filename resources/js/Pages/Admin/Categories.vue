<template>
  <AdminLayout title="Data Kategori">
    <!-- Header bar -->
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-gray-500">{{ categories.length }} kategori terdaftar</p>
      <button @click="openCreate" class="bg-amber-700 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors flex items-center gap-2">
        <span class="text-base leading-none">+</span> Tambah Kategori
      </button>
    </div>

    <!-- Flash -->
    <div v-if="flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
      {{ flash.success }}
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">Nama Kategori</th>
              <th class="px-5 py-3">Jumlah Menu</th>
              <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3">
                <span class="text-gray-500 text-base font-semibold">{{ cat.name }}</span>
              </td>
              <td class="px-5 py-3 text-gray-500">{{ cat.menus_count }} menu</td>
              <td class="px-5 py-3 text-center space-x-2">
                <button @click="openEdit(cat)" class="py-1.5 px-3 border border-gray-300 text-gray-700 bg-white rounded-md text-sm font-medium hover:bg-primary-100 transition focus:outline-none focus:ring-2 focus:ring-primary-300">
                  Edit
                </button>
                <button @click="confirmDelete(cat)" class="py-1.5 px-3 border border-red-300 text-red-600 bg-white rounded-md text-sm font-medium hover:bg-red-50 transition focus:outline-none focus:ring-2 focus:ring-red-300">
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="categories.length === 0">
              <td colspan="3" class="px-5 py-10 text-center text-gray-400">Belum ada kategori. Klik "Tambah Kategori" untuk mulai.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Create/Edit -->
    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="modal.open = false" />
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">{{ modal.isEdit ? 'Edit Kategori' : 'Tambah Kategori Baru' }}</h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
              <input
                v-model="form.name"
                type="text"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition"
                placeholder="Contoh: Bakmi, Minuman, Snack"
                required
              />
              <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="modal.open = false" class="flex-1 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                Batal
              </button>
              <button type="submit" :disabled="submitting" class="flex-1 py-2 bg-amber-700 hover:bg-amber-600 disabled:opacity-60 text-white rounded-xl text-sm font-semibold transition-colors">
                {{ submitting ? 'Menyimpan...' : (modal.isEdit ? 'Simpan Perubahan' : 'Tambah Kategori') }}
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
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Hapus Kategori?</h3>
          <p class="text-sm text-gray-500 mb-6">
            Yakin ingin menghapus kategori <strong class="capitalize">{{ deleteTarget.name }}</strong>?
            <span v-if="deleteTarget.menus_count > 0" class="block mt-1 text-amber-600">
              Terdapat {{ deleteTarget.menus_count }} menu yang menggunakan kategori ini.
            </span>
          </p>
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
  categories: { type: Array, default: () => [] },
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const errors = computed(() => page.props.errors ?? {})
const submitting = ref(false)
const deleteTarget = ref(null)

const form = ref({ name: '' })
const modal = ref({ open: false, isEdit: false, editId: null })

function openCreate() {
  form.value = { name: '' }
  modal.value = { open: true, isEdit: false, editId: null }
}

function openEdit(cat) {
  form.value = { name: cat.name }
  modal.value = { open: true, isEdit: true, editId: cat.id }
}

function submitForm() {
  submitting.value = true
  if (modal.value.isEdit) {
    router.put(route('admin.categories.update', modal.value.editId), form.value, {
      onFinish: () => { submitting.value = false; modal.value.open = false },
    })
  } else {
    router.post(route('admin.categories.store'), form.value, {
      onFinish: () => { submitting.value = false; modal.value.open = false },
    })
  }
}

function confirmDelete(cat) {
  deleteTarget.value = cat
}

function doDelete() {
  submitting.value = true
  router.delete(route('admin.categories.destroy', deleteTarget.value.id), {
    onFinish: () => { submitting.value = false; deleteTarget.value = null },
  })
}
</script>
