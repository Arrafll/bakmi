<template>
    <AdminLayout title="Laporan">
        <!-- Page header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Laporan Penjualan</h2>
                <p class="text-sm text-gray-500 mt-0.5">Ekspor data pesanan selesai ke format Excel</p>
            </div>
            <button
                @click="openModal"
                class="bg-amber-700 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors flex items-center gap-2"
            >
                <ArrowDownTrayIcon class="w-4 h-4" />
                Export Excel
            </button>
        </div>

        <!-- Info card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 text-center text-gray-400">
            <DocumentChartBarIcon class="w-12 h-12 mx-auto mb-3 text-amber-200" />
            <p class="text-sm">Klik tombol <span class="font-semibold text-amber-700">Export Excel</span> untuk mengunduh laporan pesanan selesai.</p>
            <p class="text-xs mt-1">Anda dapat memfilter berdasarkan rentang tanggal sesuai kebutuhan.</p>
        </div>

        <!-- ── Export Modal ─────────────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                    @click.self="closeModal"
                >
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showModal"
                            class="bg-white rounded-2xl shadow-xl w-full max-w-md"
                        >
                            <!-- Modal header -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <ArrowDownTrayIcon class="w-5 h-5 text-amber-700" />
                                    <h3 class="font-semibold text-gray-800">Export Data Pesanan</h3>
                                </div>
                                <button @click="closeModal" class="p-1 rounded-lg hover:bg-gray-100 transition-colors">
                                    <XMarkIcon class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <!-- Modal body -->
                            <div class="px-6 py-5 space-y-4">
                                <p class="text-sm text-gray-500">
                                    Pilih rentang tanggal untuk mengekspor data pesanan yang telah <span class="font-medium text-green-600">selesai</span>.
                                </p>

                                <!-- Date range -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                                        <input
                                            v-model="form.start_date"
                                            type="date"
                                            :max="form.end_date || undefined"
                                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        />
                                        <p v-if="errors.start_date" class="text-red-500 text-xs mt-1">{{ errors.start_date }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                                        <input
                                            v-model="form.end_date"
                                            type="date"
                                            :min="form.start_date || undefined"
                                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        />
                                        <p v-if="errors.end_date" class="text-red-500 text-xs mt-1">{{ errors.end_date }}</p>
                                    </div>
                                </div>

                                <!-- Error message -->
                                <p v-if="errors.general" class="text-red-500 text-sm bg-red-50 rounded-lg px-3 py-2">{{ errors.general }}</p>
                            </div>

                            <!-- Modal footer -->
                            <div class="px-6 pb-5 flex gap-3 justify-end">
                                <button
                                    @click="closeModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-colors"
                                    :disabled="loading"
                                >
                                    Batal
                                </button>
                                <button
                                    @click="doExport"
                                    :disabled="loading || !form.start_date || !form.end_date"
                                    class="px-5 py-2 text-sm font-semibold bg-amber-700 hover:bg-amber-600 disabled:bg-amber-300 disabled:cursor-not-allowed text-white rounded-xl transition-colors flex items-center gap-2"
                                >
                                    <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                                    </svg>
                                    <ArrowDownTrayIcon v-else class="w-4 h-4" />
                                    {{ loading ? 'Memproses...' : 'Export' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowDownTrayIcon, DocumentChartBarIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import axios from 'axios'
import * as XLSX from 'xlsx'
import { route } from 'ziggy-js'

const showModal = ref(false)
const loading = ref(false)

const form = reactive({
    start_date: '',
    end_date: '',
})

const errors = reactive({
    start_date: '',
    end_date: '',
    general: '',
})

function openModal() {
    // Default to current month
    const now = new Date()
    const y = now.getFullYear()
    const m = String(now.getMonth() + 1).padStart(2, '0')
    const d = String(now.getDate()).padStart(2, '0')
    form.start_date = `${y}-${m}-01`
    form.end_date = `${y}-${m}-${d}`
    clearErrors()
    showModal.value = true
}

function closeModal() {
    if (loading.value) return
    showModal.value = false
}

function clearErrors() {
    errors.start_date = ''
    errors.end_date = ''
    errors.general = ''
}

async function doExport() {
    clearErrors()

    if (!form.start_date) { errors.start_date = 'Tanggal awal wajib diisi.'; return }
    if (!form.end_date)   { errors.end_date   = 'Tanggal akhir wajib diisi.'; return }

    loading.value = true
    try {
        const response = await axios.get(route('admin.reports.data'), {
            params: { start_date: form.start_date, end_date: form.end_date },
        })

        const { orders, total_revenue, total_orders, start_date, end_date } = response.data

        buildAndDownloadExcel({ orders, total_revenue, total_orders, start_date, end_date })
        showModal.value = false
    } catch (err) {
        if (err.response?.status === 422) {
            const validationErrors = err.response.data.errors || {}
            errors.start_date = validationErrors.start_date?.[0] ?? ''
            errors.end_date   = validationErrors.end_date?.[0]   ?? ''
            if (!errors.start_date && !errors.end_date) {
                errors.general = 'Terjadi kesalahan validasi.'
            }
        } else {
            errors.general = 'Gagal mengambil data. Coba lagi.'
        }
    } finally {
        loading.value = false
    }
}

function buildAndDownloadExcel({ orders, total_revenue, total_orders, start_date, end_date }) {
    const formatRupiah = (val) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)

    // ── Title rows ─────────────────────────────────────────────────
    const titleRows = [
        ['LAPORAN PENJUALAN - BAKMI JAWA MAS AGUS'],
        [`Periode: ${start_date} s/d ${end_date}`],
        [`Total Pesanan Selesai: ${total_orders}`],
        [`Total Pendapatan: ${formatRupiah(total_revenue)}`],
        [],
    ]

    // ── Header row ─────────────────────────────────────────────────
    const headers = [
        'No.',
        'ID Pesanan',
        'Nama Pelanggan',
        'No. Telepon',
        'Meja',
        'Item Pesanan',
        'Voucher',
        'Diskon (Rp)',
        'Total (Rp)',
        'Waktu Pesanan',
    ]

    // ── Data rows ─────────────────────────────────────────────────
    const dataRows = orders.map((o, i) => [
        i + 1,
        o.id,
        o.customer_name,
        o.customer_phone,
        o.table,
        o.items,
        o.voucher_code !== '-' ? o.voucher_code : '',
        o.discount_amount,
        o.total_price,
        o.created_at,
    ])

    // ── Summary row ────────────────────────────────────────────────
    const summaryRows = [
        [],
        ['', '', '', '', '', '', '', 'TOTAL PENDAPATAN', formatRupiah(total_revenue), ''],
    ]

    const allRows = [...titleRows, headers, ...dataRows, ...summaryRows]

    const ws = XLSX.utils.aoa_to_sheet(allRows)

    // Column widths
    ws['!cols'] = [
        { wch: 5 },   // No.
        { wch: 10 },  // ID
        { wch: 22 },  // Nama
        { wch: 16 },  // Telepon
        { wch: 10 },  // Meja
        { wch: 40 },  // Items
        { wch: 12 },  // Voucher
        { wch: 14 },  // Diskon
        { wch: 16 },  // Total
        { wch: 18 },  // Waktu
    ]

    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Laporan Penjualan')

    const fileName = `laporan-penjualan_${form.start_date}_sd_${form.end_date}.xlsx`
    XLSX.writeFile(wb, fileName)
}
</script>
