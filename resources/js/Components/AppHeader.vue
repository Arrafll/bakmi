<template>
    <header class="bg-amber-800 text-white shadow-lg">
        <link rel="icon" type="image/jpeg" href="/images/logo.jpeg">
            <title>{{ title }}</title>
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
                <!-- Logo + subtitle -->
                <div class="flex items-center gap-3 flex-1">
                    <img :src="'/images/logo.jpeg'" alt="Bakmi Jawa Mas Agus"
                        class="h-16 w-16 object-contain rounded-full bg-white p-0.5 flex-shrink-0" />
                    <div>
                        <h1 class="text-xl font-bold leading-tight">{{ title }}</h1>
                        <p class="text-amber-200 text-sm mt-0.5">{{ subtitle || 'Bakmi Jawa Cita Rasa Khas' }}</p>
                    </div>
                </div>
                <Link :href="route('cart.index')"
                    class="relative flex items-center gap-2 bg-amber-700 hover:bg-amber-600 transition-colors px-4 py-2 rounded-full flex-shrink-0">
                    <ShoppingCartIcon class="w-5 h-5 text-white-500" />
                    <span class="text-sm font-semibold hidden sm:inline">Keranjang</span>
                    <span v-if="cartCount > 0"
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                        {{ cartCount > 99 ? '99+' : cartCount }}
                    </span>
                </Link>
            </div>
    </header>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ShoppingCartIcon } from '@heroicons/vue/24/outline'

defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
})

const page = usePage()
const cartCount = computed(() => page.props.cartCount ?? 0)
</script>
