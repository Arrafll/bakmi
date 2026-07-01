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

        <!-- Charts row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
            <!-- Weekly orders bar chart -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-5">Pesanan 7 Hari Terakhir</h2>
                <div class="flex items-end gap-2 h-32">
                    <div v-for="(d, i) in weeklyOrders" :key="i" class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs text-gray-500">{{ d.count }}</span>
                        <div class="w-full bg-amber-500 rounded-t-md"
                            :style="{ height: Math.max(4, d.count / maxWeekly * 100) + 'px' }" />
                        <span class="text-xs text-gray-400">{{ d.day }}</span>
                    </div>
                </div>
            </div>

            <!-- Favorite menus -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4">Menu Favorit</h2>
                <div v-if="favoriteMenus.length === 0" class="py-8 text-center text-gray-400 text-sm">
                    Belum ada data pesanan
                </div>
                <div v-else class="space-y-3">
                    <div v-for="(c, i) in favoriteMenus" :key="i">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700">{{ c.label }}</span>
                            <span class="text-gray-400 font-medium">{{ c.percent }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div :class="[c.color, 'h-full rounded-full']" :style="{ width: c.percent + '%' }" />
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
                                    {{ order.voucher_code }}
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
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ShoppingCartIcon, ClipboardDocumentCheckIcon, ClipboardDocumentListIcon, TicketIcon } from '@heroicons/vue/24/outline'
import StatusBadge from '@/Components/StatusBadge.vue'
import StatCard from '@/Components/StatCard.vue'
import { useFormat } from '@/composables/useFormat'

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
    favoriteMenus: {
        type: Array,
        default: () => []
    },
})

const { formatPrice, formatDate } = useFormat()

const weeklyOrders = props.weeklyOrders
const maxWeekly = weeklyOrders.length > 0 ? Math.max(...weeklyOrders.map(d => d.count)) : 0

const favoriteMenus = props.favoriteMenus

const statItems = [
    { icon: ShoppingCartIcon, label: 'Pesanan Hari Ini', value: props.stats.orders_today, color: 'bg-blue-500' },
    { icon: ClipboardDocumentCheckIcon, label: 'Pesanan Selesai', value: props.stats.completed_orders_today, color: 'bg-green-500' },
    { icon: ClipboardDocumentListIcon, label: 'Total Menu Aktif', value: props.stats.active_menus, color: 'bg-amber-500' },
    { icon: TicketIcon, label: 'Voucher Aktif', value: props.stats.active_vouchers, color: 'bg-purple-500' },
]
</script>
