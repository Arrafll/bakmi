<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-40 w-64 bg-amber-900 text-white flex flex-col transition-transform duration-300',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            'lg:translate-x-0 lg:static lg:inset-auto',
        ]">
            <!-- Brand -->
            <div class="px-4 py-4 border-b border-amber-700 flex items-center gap-3">
                <img :src="'/images/logo.jpeg'" alt="Bakmi Jawa Mas Agus"
                    class="h-12 w-12 object-contain rounded-full bg-white p-0.5 flex-shrink-0" />
                <div>
                    <p class="font-bold text-base leading-tight">Bakmi Admin</p>
                    <p class="text-amber-300 text-xs">Halaman Manajemen</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a v-for="item in menus" :key="item.route" :href="route(item.route)" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                    isActive(route(item.route))
                        ? 'bg-amber-700 text-white'
                        : 'text-amber-100 hover:bg-amber-800',
                ]">
                    <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                    {{ item.label }}
                </a>
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
                            <ArrowLeftOnRectangleIcon class="w-4 h-4" />
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { HomeIcon, ClipboardDocumentListIcon, TicketIcon, ArrowLeftOnRectangleIcon } from '@heroicons/vue/24/outline'

defineProps({
    title: {
        type: String,
        default: 'Admin',
    },
})

const page = usePage()
const user = page.props.auth?.user ?? { name: 'Admin' }

const sidebarOpen = ref(false)

function logout() {
    router.post(route('admin.logout'))
}


const menus = [
    {
        label: 'Dashboard',
        icon: HomeIcon,
        route: 'admin.dashboard',
    },
    {
        label: 'Master Menu',
        icon: ClipboardDocumentListIcon,
        route: 'admin.menus.index',
    },
    {
        label: 'Voucher Promo',
        icon: TicketIcon,
        route: 'admin.vouchers.index',
    },
]

const isActive = (href) => {
    const current = page.url
    const target = new URL(href, window.location.origin).pathname
    return current.startsWith(target)
}

</script>
