<template>
    <head>
        <title>Admin Login - Bakmi</title>
        <link rel="icon" type="image/x-icon" href="/images/logo.ico" />
    </head>
    <div class="min-h-screen bg-amber-50 flex items-center justify-center px-4">
        <div class="w-full max-w-md">

            <div class="text-center mb-8">
                <img :src="'/images/logo.jpeg'" alt="Bakmi Jawa Mas Agus"
                    class="mx-auto h-32 w-32 object-contain rounded-full bg-white p-1 shadow-md" />

                <h1 class="text-3xl font-bold text-amber-800 mt-4">
                    Bakmi Admin
                </h1>

                <p class="text-amber-600 mt-1 text-sm">
                    Masuk untuk mengelola restoran bakmi
                </p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <form @submit.prevent="submit" novalidate>

                    <!-- Email -->
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">
                            Email
                        </label>
                        <input id="email" v-model="form.email" type="email" autocomplete="email"
                            placeholder="admin@example.com" :class="[
                                'w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition',
                                errors.email
                                    ? 'border-red-400 focus:ring-2 focus:ring-red-200'
                                    : 'border-gray-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-100',
                            ]" />
                        <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">
                            Password
                        </label>
                        <div class="relative">
                            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password" placeholder="••••••••" :class="[
                                    'w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition pr-10',
                                    errors.password
                                        ? 'border-red-400 focus:ring-2 focus:ring-red-200'
                                        : 'border-gray-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-100',
                                ]" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                                {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                            </button>
                        </div>
                        <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password }}</p>
                    </div>

                    <!-- Remember me -->
                    <div class="mb-6 flex items-center gap-2">
                        <input id="remember" v-model="form.remember" type="checkbox"
                            class="w-4 h-4 accent-amber-600 cursor-pointer" />
                        <label for="remember" class="text-sm text-gray-600 cursor-pointer">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-amber-700 hover:bg-amber-800 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg transition-colors duration-200">
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </button>

                </form>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">
                © {{ year }} Bakmi — Panel Admin
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
})

const showPassword = ref(false)
const year = new Date().getFullYear()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>
