<template>
  <div class="min-h-screen bg-amber-50">
    <AppHeader title="" subtitle="Sajian lezat pilihan kami" />

    <div class="max-w-6xl mx-auto px-4 mt-8">
      <CategoryFilter :categories="categoryList" v-model="activeCategory" />
    </div>

    <main class="max-w-6xl mx-auto px-4 py-10">
      <div
        v-if="filteredMenus.length"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8"
      >
        <MenuCard v-for="item in filteredMenus" :key="item.id" :item="item" />
      </div>

      <div v-else class="text-center py-20 text-gray-400">
        <p class="text-5xl mb-4">🍽️</p>
        <p class="text-xl">Tidak ada menu di kategori ini.</p>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import MenuCard from '@/Components/MenuCard.vue'
import CategoryFilter from '@/Components/CategoryFilter.vue'

const props = defineProps({
  menus: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: null,
  },
})

const activeCategory = ref('all')

const categoryList = computed(() => {
  if (props.categories) {
    return ['all', ...props.categories.map((c) => (typeof c === 'object' ? c.name : c))]
  }
  const unique = [...new Set(props.menus.map((m) => m.category))]
  return ['all', ...unique]
})

const filteredMenus = computed(() => {
  if (activeCategory.value === 'all') return props.menus
  return props.menus.filter((m) => m.category === activeCategory.value)
})
</script>
