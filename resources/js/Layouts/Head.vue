<template>
    <Head>
        <link rel="icon" type="image/x-icon" :href="asset('/images/logo.ico')" />
        <title>{{ title }} | Bakmi Jawa Mas Agus</title>
    </Head>
</template>

<script setup>
import { asset } from '@/utils/asset'

defineProps({
    title: {
        type: String,
        required: true,
    },
})
</script>