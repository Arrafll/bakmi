<template>
  <AdminLayout title="Manajemen Pesanan">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-gray-500">{{ orders.length }} pesanan</p>
      <!-- Filter by status -->
      <div class="flex gap-2 flex-wrap">
        <button
          v-for="s in ['all', ...statuses]" :key="s"
          @click="activeFilter = s"
          :class="[
            'px-3 py-1.5 rounded-full text-xs font-semibold transition-colors',
            activeFilter === s
              ? 'bg-amber-700 text-white'
              : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50',
          ]"
        >
          {{ s === 'all' ? 'Semua' : statusLabel(s) }}
          <span class="ml-1 opacity-70">({{ s === 'all' ? orders.length : orders.filter(o => o.status === s).length }})</span>
        </button>
      </div>
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
              <th class="px-5 py-3">#</th>
              <th class="px-5 py-3">Pelanggan</th>
              <th class="px-5 py-3">Meja</th>
              <th class="px-5 py-3">Total</th>
              <th class="px-5 py-3">Status</th>
              <th class="px-5 py-3">Waktu Pesan</th>
              <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3 text-gray-400 font-mono text-xs">#{{ order.id }}</td>
              <td class="px-5 py-3">
                <p class="font-medium text-gray-800">{{ order.customer_name }}</p>
                <p class="text-gray-400 text-xs">{{ order.customer_phone }}</p>
              </td>
              <td class="px-5 py-3 text-gray-600">
                {{ order.table?.name ?? '—' }}
              </td>
              <td class="px-5 py-3 font-medium text-gray-700">{{ formatPrice(order.total_price) }}</td>
              <td class="px-5 py-3">
                <StatusBadge :status="order.status" />
              </td>
              <td class="px-5 py-3 text-gray-500 text-xs">{{ formatDate(order.created_at) }}</td>
              <td class="px-5 py-3 text-center space-x-2">
                <button @click="openDetail(order)" class="py-1.5 px-3 border border-gray-300 text-gray-700 bg-white rounded-md text-xs font-medium hover:bg-gray-100 transition">
                  Detail
                </button>
                <button @click="openStatus(order)" class="py-1.5 px-3 border border-amber-400 text-amber-700 bg-white rounded-md text-xs font-medium hover:bg-amber-50 transition">
                  Proses
                </button>
              </td>
            </tr>
            <tr v-if="filteredOrders.length === 0">
              <td colspan="7" class="px-5 py-10 text-center text-gray-400">Tidak ada pesanan.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
      <div v-if="detailOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="detailOrder = null" />
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">Detail Pesanan #{{ detailOrder.id }}</h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ detailOrder.customer_name }} · {{ detailOrder.table?.name ?? 'Tanpa meja' }}</p>
            </div>
            <StatusBadge :status="detailOrder.status" />
          </div>

          <div class="p-6 space-y-4">
            <!-- Info -->
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-gray-400 text-xs mb-0.5">No. HP</p>
                <p class="font-medium text-gray-700">{{ detailOrder.customer_phone }}</p>
              </div>
              <div>
                <p class="text-gray-400 text-xs mb-0.5">Waktu Pesan</p>
                <p class="font-medium text-gray-700">{{ formatDate(detailOrder.created_at) }}</p>
              </div>
              <div v-if="detailOrder.notes" class="col-span-2">
                <p class="text-gray-400 text-xs mb-0.5">Catatan</p>
                <p class="font-medium text-gray-700">{{ detailOrder.notes }}</p>
              </div>
            </div>

            <!-- Items -->
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Item Pesanan</p>
              <div class="space-y-2">
                <div
                  v-for="item in detailOrder.items" :key="item.id"
                  class="flex items-center justify-between bg-amber-50 rounded-xl px-4 py-2.5"
                >
                  <div>
                    <p class="font-medium text-gray-800 text-sm">{{ item.menu?.name ?? 'Menu dihapus' }}</p>
                    <p class="text-xs text-gray-500">{{ formatPrice(item.price) }} × {{ item.quantity }}</p>
                  </div>
                  <p class="font-bold text-gray-800 text-sm">{{ formatPrice(item.price * item.quantity) }}</p>
                </div>
              </div>
            </div>

            <!-- Totals -->
            <div class="border-t border-gray-100 pt-3 space-y-1 text-sm">
              <div v-if="detailOrder.voucher_code" class="flex justify-between text-gray-500">
                <span>Voucher ({{ detailOrder.voucher_code }})</span>
                <span class="text-green-600">– {{ formatPrice(detailOrder.discount_amount) }}</span>
              </div>
              <div class="flex justify-between font-bold text-gray-800 text-base">
                <span>Total</span>
                <span>{{ formatPrice(detailOrder.total_price) }}</span>
              </div>
            </div>

            <button @click="detailOrder = null" class="w-full py-2 border border-gray-300 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition-colors">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Status Modal -->
    <Teleport to="body">
      <div v-if="statusTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="statusTarget = null" />
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-1">Ubah Status Pesanan</h3>
          <p class="text-sm text-gray-400 mb-4">#{{ statusTarget.id }} · {{ statusTarget.customer_name }}</p>

          <div class="grid grid-cols-2 gap-2 mb-5">
            <button
              v-for="s in statuses" :key="s"
              @click="selectedStatus = s"
              :class="[
                'py-2 px-3 rounded-xl text-sm font-semibold border-2 transition-colors',
                selectedStatus === s
                  ? statusBorderClass(s) + ' ' + statusClass(s)
                  : 'border-gray-200 text-gray-500 hover:border-gray-300',
              ]"
            >
              {{ statusLabel(s) }}
            </button>
          </div>

          <div class="flex gap-3">
            <button @click="statusTarget = null" class="flex-1 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition-colors">Batal</button>
            <button @click="doUpdateStatus" :disabled="submitting || selectedStatus === statusTarget.status" class="flex-1 py-2 bg-amber-700 hover:bg-amber-600 disabled:opacity-50 text-white rounded-xl text-sm font-semibold transition-colors">
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
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
import StatusBadge from '@/Components/StatusBadge.vue'
import { useFormat } from '@/composables/useFormat'

const props = defineProps({
  orders: { type: Array, default: () => [] },
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const { formatPrice, formatDate } = useFormat()

const statuses = ['dipesan', 'diproses', 'selesai', 'dibatalkan']
const activeFilter = ref('all')
const detailOrder = ref(null)
const statusTarget = ref(null)
const selectedStatus = ref('')
const submitting = ref(false)

const filteredOrders = computed(() => {
  if (activeFilter.value === 'all') return props.orders
  return props.orders.filter(o => o.status === activeFilter.value)
})

function statusLabel(s) {
  return { dipesan: 'Dipesan', diproses: 'Diproses', selesai: 'Selesai', dibatalkan: 'Dibatalkan' }[s] ?? s
}

function statusClass(s) {
  return {
    dipesan:    'bg-orange-100 text-orange-700',
    diproses:   'bg-blue-100 text-blue-700',
    selesai:    'bg-green-100 text-green-700',
    dibatalkan: 'bg-red-100 text-red-600',
  }[s] ?? 'bg-gray-100 text-gray-500'
}

function statusBorderClass(s) {
  return {
    dipesan:    'border-orange-400',
    diproses:   'border-blue-400',
    selesai:    'border-green-400',
    dibatalkan: 'border-red-400',
  }[s] ?? 'border-gray-300'
}

function openDetail(order) {
  detailOrder.value = order
}

function openStatus(order) {
  statusTarget.value = order
  selectedStatus.value = order.status
}

function doUpdateStatus() {
  submitting.value = true
  router.put(route('admin.orders.update-status', statusTarget.value.id), { status: selectedStatus.value }, {
    onFinish: () => { submitting.value = false; statusTarget.value = null },
  })
}
</script>
