<template>
  <div class="flex items-center gap-3">
    <div :class="['p-2 rounded-lg text-white', color]">
      <component :is="icon" class="w-5 h-5" />
    </div>
    <div>
      <p class="text-sm text-gray-500">{{ label }}</p>
      <p class="text-lg font-semibold">{{ value }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: Object,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  color: {
    type: String,
    required: true,
  },
})
</script>
