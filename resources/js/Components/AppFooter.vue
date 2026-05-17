<template>
  <footer class="text-center py-6 text-amber-700 text-sm border-t border-amber-200">
    © {{ year }} {{ brand }} — {{ tagline }}
  </footer>
</template>

<script setup>
defineProps({
  brand: {
    type: String,
    default: 'Bakmi',
  },
  tagline: {
    type: String,
    default: 'Made by dreowsy',
  },
})

const year = new Date().getFullYear()
</script>
