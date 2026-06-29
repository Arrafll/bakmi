<template>
    <Head>
        <link rel="icon" type="image/x-icon" :href="icon" />
        <title>{{ title }} | Bakmi Admin</title>
    </Head>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-40 w-64 bg-amber-900 text-white flex flex-col transition-transform duration-300',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            'lg:translate-x-0 lg:static lg:inset-auto',
        ]">
            <!-- Brand -->
            <div class="px-4 py-4 border-b border-amber-700 flex items-center gap-3">
                <img :src="logo" :alt="title"
                    class="h-12 w-12 object-contain rounded-full bg-white p-0.5 flex-shrink-0" />
                <div>
                    <p class="font-bold text-base leading-tight">{{ title }}</p>
                    <p class="text-amber-300 text-xs">Halaman Manajemen</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <Link v-for="item in menus" :key="item.route" :href="route(item.route)" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                    isActive(route(item.route))
                        ? 'bg-amber-700 text-white'
                        : 'text-amber-100 hover:bg-amber-800',
                ]">
                    <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                    {{ item.label }}
                </Link>
            </nav>

        </aside>

        <!-- Overlay (mobile) -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="sidebarOpen = false" />

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar -->
            <header class="bg-white shadow-sm px-4 py-3 flex items-center gap-4 sticky top-0 z-20">
                <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    @click="sidebarOpen = !sidebarOpen" aria-label="Toggle menu">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800 flex-1">{{ title }}</h1>

                <!-- User & Logout -->
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500 hidden sm:block">{{ user.name }}</span>
                    <form @submit.prevent="logout">
                        <button type="submit"
                            class="flex items-center gap-1.5 text-sm text-gray-600 hover:text-red-600 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                            <ArrowLeftEndOnRectangleIcon class="w-4 h-4" />
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

        <!-- Page content -->
        <main class="flex-1 p-6 overflow-auto">
            <slot />
        </main>

        <!-- Order Notification Toast -->
        <OrderNotification ref="orderNotification" />

        <!-- Debug Pusher Status (remove in production) -->
        <div v-if="false" class="fixed bottom-4 left-4 bg-black text-white text-xs p-2 rounded z-50">
            Pusher: {{ pusherConnected ? 'Connected' : 'Disconnected' }}
        </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { HomeIcon, ClipboardDocumentListIcon, TicketIcon, QrCodeIcon, TagIcon, ArrowLeftEndOnRectangleIcon } from '@heroicons/vue/24/outline'
import OrderNotification from '@/Components/OrderNotification.vue'
import Pusher from 'pusher-js'
import Swal from 'sweetalert2'

defineProps({
    title: {
        type: String,
        default: 'Admin',
    },
    logo: {
        type: String,
        default: '/images/logo.jpeg',
    },
    icon: {
        type: String,
        default: '/images/logo.ico',
    },
})

const page = usePage()
const user = page.props.auth?.user ?? { name: 'Admin' }
const pusherConfig = page.props.pusher || {}

const sidebarOpen = ref(false)
const orderNotification = ref(null)
let pusher = null
let channel = null
const pusherConnected = ref(false)

function logout() {
    Swal.fire({
        title: 'Keluar dari Akun?',
        text: 'Anda akan keluar dari panel admin',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.logout'))
        }
    })
}

function initPusher() {
    try {
        if (!pusherConfig.key) {
            console.warn('Pusher key not configured')
            return
        }

        pusher = new Pusher(pusherConfig.key, {
            cluster: pusherConfig.cluster || 'ap1',
            forceTLS: true,
        })

        console.log('Pusher initialized')

        pusher.connection.bind('connected', () => {
            console.log('Pusher connected')
            pusherConnected.value = true
        })

        pusher.connection.bind('disconnected', () => {
            console.log('Pusher disconnected')
            pusherConnected.value = false
        })

        pusher.connection.bind('error', (err) => {
            console.error('Pusher connection error:', err)
        })

        channel = pusher.subscribe('admin-notifications')
        console.log('Subscribed to admin-notifications channel')

        channel.bind('new-order', (data) => {
            console.log('Received new-order event:', data)
            if (orderNotification.value && data.order) {
                orderNotification.value.showNotification(data.order)

                // Auto-refresh if currently on orders page
                const currentPath = window.location.pathname
                if (currentPath.includes('/admin/orders')) {
                    setTimeout(() => {
                        router.reload({ only: ['orders'] })
                    }, 2000)
                }
            }
        })

        channel.bind('pusher:subscription_error', (err) => {
            console.error('Channel subscription error:', err)
        })
    } catch (error) {
        console.error('Failed to initialize Pusher:', error)
    }
}

onMounted(() => {
    initPusher()
})

onUnmounted(() => {
    if (channel) {
        channel.unbind('new-order')
        pusher.unsubscribe('admin-notifications')
    }
    if (pusher) {
        pusher.disconnect()
    }
})


const menus = [
    {
        label: 'Dashboard',
        icon: HomeIcon,
        route: 'admin.dashboard',
    },
    {
        label: 'Pesanan',
        icon: ClipboardDocumentListIcon,
        route: 'admin.orders.index',
    },
    {
        label: 'Data Kategori',
        icon: TagIcon,
        route: 'admin.categories.index',
    },
    {
        label: 'Data Menu',
        icon: ClipboardDocumentListIcon,
        route: 'admin.menus.index',
    },
    {
        label: 'Voucher Promo',
        icon: TicketIcon,
        route: 'admin.vouchers.index',
    },
    {
        label: 'Meja',
        icon: QrCodeIcon,
        route: 'admin.tables.index',
    },
]

const isActive = (href) => {
    const current = page.url
    const target = new URL(href, window.location.origin).pathname
    return current.startsWith(target)
}

</script>
