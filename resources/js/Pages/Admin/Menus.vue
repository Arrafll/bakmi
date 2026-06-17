<template>
  <AdminLayout title="Master Menu">
    <!-- Header bar -->
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-gray-500">{{ menus.length }} item menu</p>
      <button @click="openCreate" class="bg-amber-700 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors flex items-center gap-2">
        <span class="text-base leading-none">+</span> Tambah Menu
      </button>
    </div>

    <!-- Flash messages -->
    <div v-if="flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
      {{ flash.success }}
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
              <th class="px-5 py-3 w-16">Foto</th>
              <th class="px-5 py-3">Nama</th>
              <th class="px-5 py-3">Kategori</th>
              <th class="px-5 py-3">Harga</th>
              <th class="px-5 py-3">Tersedia</th>
              <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="menu in menus" :key="menu.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3">
                <div class="w-12 h-12 rounded-xl overflow-hidden bg-amber-100 flex items-center justify-center text-xl flex-shrink-0">
                  <img v-if="menu.image" :src="menu.image" :alt="menu.name" class="w-full h-full object-cover" />
                  <span v-else>🍜</span>
                </div>
              </td>
              <td class="px-5 py-3">
                <p class="font-medium text-gray-800">{{ menu.name }}</p>
                <p class="text-gray-400 text-xs truncate max-w-xs">{{ menu.description }}</p>
              </td>
              <td class="px-5 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-medium capitalize">{{ menu.category }}</span>
              </td>
              <td class="px-5 py-3 font-medium text-gray-700">{{ formatPrice(menu.price) }}</td>
              <td class="px-5 py-3">
                <span :class="menu.is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded-full text-xs font-medium">
                  {{ menu.is_available ? 'Tersedia' : 'Habis' }}
                </span>
              </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <button @click="openEdit(menu)" class="flex-1 py-2 px-3 border border-primary-400 text-gray-700 bg-white rounded-md text-sm font-medium hover:bg-gray-100 hover:border-gray-500 active:bg-gray-200 transition focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Edit
                    </button>
                    <button @click="confirmDelete(menu)" class="flex-1 py-2 px-3 border border-red-300 text-red-600 bg-white rounded-md text-sm font-medium hover:bg-red-50 hover:border-red-400 active:bg-red-100 transition focus:outline-none focus:ring-2 focus:ring-red-300">
                        Hapus
                    </button>
                </td>

            </tr>
            <tr v-if="menus.length === 0">
              <td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada menu. Klik "Tambah Menu" untuk mulai.</td>
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
            <h3 class="text-lg font-semibold text-gray-800">{{ modal.isEdit ? 'Edit Menu' : 'Tambah Menu Baru' }}</h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="Contoh: Bakmi Goreng Special" required />
              <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
              <textarea v-model="form.description" rows="2" class="input resize-none w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="Deskripsi singkat menu..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                <input v-model.number="form.price" type="number" min="0" step="500" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" required />
                <p v-if="errors.price" class="text-red-500 text-xs mt-1">{{ errors.price }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select v-model="form.category" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" required>
                  <option value="">Pilih kategori</option>
                  <option v-for="cat in categories" :key="cat" :value="cat" class="capitalize">{{ cat }}</option>
                </select>
                <p v-if="errors.category" class="text-red-500 text-xs mt-1">{{ errors.category }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">URL Gambar</label>
              <input v-model="form.image" type="url" class="input w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition" placeholder="https://example.com/image.jpg" />
            </div>

            <div class="flex items-center gap-3">
              <input v-model="form.is_available" id="is_available" type="checkbox" class="w-4 h-4 accent-amber-600" />
              <label for="is_available" class="text-sm font-medium text-gray-700">Menu tersedia</label>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="modal.open = false" class="flex-1 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                Batal
              </button>
              <button type="submit" :disabled="submitting" class="flex-1 py-2 bg-amber-700 hover:bg-amber-600 disabled:opacity-60 text-white rounded-xl text-sm font-semibold transition-colors">
                {{ submitting ? 'Menyimpan...' : (modal.isEdit ? 'Simpan Perubahan' : 'Tambah Menu') }}
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
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Hapus Menu?</h3>
          <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus <strong>{{ deleteTarget.name }}</strong>? Tindakan ini tidak bisa dibatalkan.</p>
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
  menus: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const errors = computed(() => page.props.errors ?? {})
const submitting = ref(false)
const deleteTarget = ref(null)

const emptyForm = () => ({
  name: '',
  description: '',
  price: '',
  category: '',
  image: '',
  is_available: true,
})

const form = ref(emptyForm())
const modal = ref({ open: false, isEdit: false, editId: null })

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
}

function openCreate() {
  form.value = emptyForm()
  modal.value = { open: true, isEdit: false, editId: null }
}

function openEdit(menu) {
  form.value = {
    name: menu.name,
    description: menu.description ?? '',
    price: menu.price,
    category: menu.category,
    image: menu.image ?? '',
    is_available: !!menu.is_available,
  }
  modal.value = { open: true, isEdit: true, editId: menu.id }
}

function submitForm() {
  submitting.value = true
  if (modal.value.isEdit) {
    router.put(route('admin.menus.update', modal.value.editId), form.value, {
      onFinish: () => { submitting.value = false; modal.value.open = false },
    })
  } else {
    router.post(route('admin.menus.store'), form.value, {
      onFinish: () => { submitting.value = false; modal.value.open = false },
    })
  }
}

function confirmDelete(menu) {
  deleteTarget.value = menu
}

function doDelete() {
  submitting.value = true
  router.delete(route('admin.menus.destroy', deleteTarget.value.id), {
    onFinish: () => { submitting.value = false; deleteTarget.value = null },
  })
}
</script>

