<template>
    <AppHeader :title="title" :subtitle="subtitle" :logo="logo" :icon="icon">
        <template #actions>
            <slot name="header-actions" />
        </template>
    </AppHeader>
    <slot />
</template>

<script setup>
import AppHeader from '@/Components/AppHeader.vue'

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
        default: '/images/logo.jpeg',
    },
    icon: {
        type: String,
        default: '/images/logo.ico',
    },
})
</script>
