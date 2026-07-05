<template>
  <div>
    <p class="text-sm font-semibold text-gray-700 mb-2">{{ criterion.name }}</p>
    <div class="flex flex-wrap gap-2">
      <button
        v-for="(label, i) in criterion.scale_labels"
        :key="i"
        type="button"
        @click="$emit('update:modelValue', i + 1)"
        class="px-3 py-2 rounded-xl text-xs font-medium border transition-colors"
        :class="modelValue === i + 1
          ? 'bg-amber-700 border-amber-700 text-white'
          : 'bg-white border-gray-300 text-gray-600 hover:border-amber-400 hover:text-amber-700'"
      >
        {{ label }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  criterion: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: Number,
    default: null,
  },
})

defineEmits(['update:modelValue'])
</script>
