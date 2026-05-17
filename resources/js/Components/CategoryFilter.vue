<template>
  <div class="flex flex-wrap gap-3 justify-center">
    <button
      v-for="cat in categories"
      :key="cat"
      @click="emit('update:modelValue', cat)"
      :class="[
        'px-5 py-2 rounded-full font-semibold text-sm transition-all duration-200 capitalize',
        modelValue === cat
          ? 'bg-amber-700 text-white shadow-md'
          : 'bg-white text-amber-700 border border-amber-300 hover:bg-amber-100',
      ]"
    >
      {{ cat === 'all' ? 'Semua' : cat }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  categories: {
    type: Array,
    required: true,
  },
  modelValue: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])
</script>
