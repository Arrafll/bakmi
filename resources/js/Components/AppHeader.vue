<template>
    <Head>
        <link rel="icon" type="image/x-icon" :href="icon" />
        <title>{{ title }} | Bakmi Jawa Mas Agus</title>
    </Head>
    <header class="bg-amber-800 text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
                <!-- Logo + subtitle -->
                <div class="flex items-center gap-3 flex-1">
                    <img :src="logo" :alt="title"
                        class="h-16 w-16 object-contain rounded-full bg-white p-0.5 flex-shrink-0" />
                    <div>
                        <h1 class="text-xl font-bold leading-tight">{{ title }}</h1>
                        <p class="text-amber-200 text-sm mt-0.5">{{ subtitle || 'Bakmi Jawa Cita Rasa Khas' }}</p>
                    </div>
                </div>

                <slot name="actions" />
            </div>
    </header>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { asset } from '@/utils/asset'

defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    logo: {
        type: String,
        default: asset('/images/logo.jpeg'),
    },
    icon: {
        type: String,
        default: asset('/images/logo.ico'),
    },
})
</script>
