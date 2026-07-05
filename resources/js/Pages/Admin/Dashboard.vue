<template>
    <AdminLayout title="Dashboard">
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
            <StatCard
                v-for="item in statItems"
                :key="item.label"
                :icon="item.icon"
                :label="item.label"
                :value="item.value"
                :color="item.color"
            />
        </div>

        <!-- Weekly orders chart + Menu Rekomendasi infographic -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
            <!-- Weekly orders bar chart -->
            <div class="bg-white rounded-2xl shadow-sm p-6 flex flex-col">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-gray-700">Pesanan 7 Hari Terakhir</h2>
                    <span class="text-xs text-gray-400">Total {{ weeklyTotal }} pesanan</span>
                </div>
                <div class="flex-1 flex items-end gap-2 min-h-[220px]">
                    <div v-for="(d, i) in weeklyOrders" :key="i" class="flex-1 flex flex-col items-center justify-end gap-1 h-full">
                        <span class="text-xs text-gray-500">{{ d.count }}</span>
                        <div class="w-full bg-amber-500 rounded-t-md min-h-[6px]"
                            :style="{ height: Math.max(4, maxWeekly > 0 ? (d.count / maxWeekly * 100) : 0) + '%' }" />
                        <span class="text-xs text-gray-400">{{ d.day }}</span>
                    </div>
                </div>
            </div>

            <!-- Menu Rekomendasi infographic -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-gray-700">Menu Rekomendasi</h2>
                    <Link :href="route('admin.recommendations.index')" class="text-xs text-amber-700 hover:text-amber-800 font-medium">
                        Lihat semua →
                    </Link>
                </div>

                <div v-if="topRecommendations.length === 0" class="py-8 text-center text-gray-400 text-sm">
                    Belum ada menu yang masuk peringkat
                </div>
                <div v-else class="space-y-4">
                    <div v-for="item in topRecommendations" :key="item.id" class="flex items-center gap-3">
                        <span class="w-6 text-center text-base flex-shrink-0">{{ medal(item.rank) }}</span>
                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-amber-100 flex items-center justify-center text-lg flex-shrink-0">
                            <img v-if="item.image_path" :src="menuImage(item.image_path)" :alt="item.name" class="w-full h-full object-cover" />
                            <span v-else>🍜</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline gap-2 text-sm mb-1">
                                <span class="text-gray-700 font-medium truncate">{{ item.name }}</span>
                                <span class="text-amber-700 font-semibold flex-shrink-0">{{ item.percentage.toFixed(1) }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-amber-500" :style="{ width: item.percentage + '%' }" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent orders table -->
        <div class="bg-white rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-700">Pesanan Terbaru</h2>
                <span class="text-xs text-gray-400">Menampilkan {{ recentOrders.length }} pesanan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
                            <th class="px-6 py-3">ID</th>
                            <th class="px-6 py-3">Pelanggan</th>
                            <th class="px-6 py-3">Total</th>
                            <th class="px-6 py-3">Voucher</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-gray-500">#{{ order.id }}</td>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ order.customer_name }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ formatPrice(order.total_price) }}</td>
                            <td class="px-6 py-3">
                                <span v-if="order.voucher_code"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700 font-medium">
                                    🎟️ {{ order.voucher_code }}
                                </span>
                                <span v-else class="text-gray-300">—</span>
                            </td>
                            <td class="px-6 py-3">
                                <StatusBadge :status="order.status" />
                            </td>
                            <td class="px-6 py-3 text-gray-500 text-xs">{{ formatDate(order.created_at) }}</td>
                        </tr>
                        <tr v-if="recentOrders.length === 0">
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada pesanan</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ShoppingCartIcon, ClipboardDocumentCheckIcon, ClipboardDocumentListIcon, TicketIcon } from '@heroicons/vue/24/outline'
import StatusBadge from '@/Components/StatusBadge.vue'
import StatCard from '@/Components/StatCard.vue'
import { useFormat } from '@/composables/useFormat'
import { menuImage } from '@/utils/asset'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            orders_today: 0,
            completed_orders_today: 0,
            active_menus: 0,
            active_vouchers: 0
        }),
    },
    recentOrders: {
        type: Array,
        default: () => []
    },
    weeklyOrders: {
        type: Array,
        default: () => []
    },
    topRecommendations: {
        type: Array,
        default: () => []
    },
})

const { formatPrice, formatDate } = useFormat()

const weeklyOrders = props.weeklyOrders
const maxWeekly = weeklyOrders.length > 0 ? Math.max(...weeklyOrders.map(d => d.count)) : 0
const weeklyTotal = weeklyOrders.reduce((sum, d) => sum + d.count, 0)

function medal(rank) {
    return { 1: '🥇', 2: '🥈', 3: '🥉' }[rank] ?? `#${rank}`
}

const statItems = [
    { icon: ShoppingCartIcon, label: 'Pesanan Hari Ini', value: props.stats.orders_today, color: 'bg-blue-500' },
    { icon: ClipboardDocumentCheckIcon, label: 'Pesanan Selesai', value: props.stats.completed_orders_today, color: 'bg-green-500' },
    { icon: ClipboardDocumentListIcon, label: 'Total Menu Aktif', value: props.stats.active_menus, color: 'bg-amber-500' },
    { icon: TicketIcon, label: 'Voucher Aktif', value: props.stats.active_vouchers, color: 'bg-purple-500' },
]
</script>
