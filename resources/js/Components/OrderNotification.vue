<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="visible"
            class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">Pesanan Baru!</p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ order.customer_name }} - Meja {{ order.table || 'N/A' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ order.items_count }} item • Rp {{ formatPrice(order.total_price) }}
                        </p>
                        <div class="mt-3 flex gap-2">
                            <Link
                                :href="route('admin.orders.index')"
                                class="text-sm font-medium text-amber-600 hover:text-amber-500"
                            >
                                Lihat Pesanan
                            </Link>
                        </div>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button
                            @click="visible = false"
                            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
                        >
                            <span class="sr-only">Tutup</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { asset } from '@/utils/asset'

const props = defineProps({
    initialOrder: Object,
})

const visible = ref(false)
const order = ref(props.initialOrder || null)
let audio = null

function formatPrice(value) {
    return new Intl.NumberFormat('id-ID').format(value)
}

function playNotificationSound() {
    try {
        audio = new Audio(asset('/sounds/order-notif.wav'))
        audio.volume = 0.5
        audio.play().catch(() => {
            // Ignore autoplay errors
        })
    } catch (e) {
        // Ignore audio errors
    }
}

function showNotification(orderData) {
    order.value = orderData
    visible.value = true
    playNotificationSound()

    // Auto hide after 8 seconds
    setTimeout(() => {
        visible.value = false
    }, 8000)
}

defineExpose({
    showNotification,
})
</script>
