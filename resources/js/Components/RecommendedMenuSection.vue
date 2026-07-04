<template>
  <section v-if="items.length" class="max-w-6xl mx-auto px-4 pt-8">
    <div class="rounded-3xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 px-5 py-6 sm:px-8 sm:py-7">
      <div class="flex items-center gap-2 mb-1">
        <span class="text-[11px] font-semibold tracking-[0.18em] uppercase text-amber-700 bg-amber-100 px-3 py-1 rounded-full">
          ✦ Rekomendasi Untuk Anda
        </span>
      </div>
      <h2 class="reco-heading text-2xl sm:text-3xl font-semibold text-amber-950 mt-2">Favorit Pilihan Hari Ini</h2>
      <p class="text-sm text-amber-800/70 mt-1 mb-5">Dipilih otomatis dari cita rasa, popularitas, porsi, dan kualitas penyajian.</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <MenuCard
          v-for="item in items"
          :key="item.id"
          :item="item"
          :percentage="item.percentage"
          :rank="item.rank"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import MenuCard from '@/Components/MenuCard.vue'

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&display=swap');

.reco-heading {
  font-family: 'Cormorant Garamond', serif;
}
</style>
