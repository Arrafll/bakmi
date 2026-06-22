<template>
  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusInfo.cls]">
    {{ statusInfo.label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
})

const STATUS_MAP = {
  pesan: { label: 'Dipesan', cls: 'bg-yellow-100 text-yellow-700' },
  diproses: { label: 'Diproses', cls: 'bg-blue-100 text-blue-700' },
  selesai: { label: 'Selesai', cls: 'bg-green-100 text-green-700' },
  dibatalkan: { label: 'Dibatalkan', cls: 'bg-red-100 text-red-700' },
}

const statusInfo = computed(() => {
  return STATUS_MAP[props.status] ?? { label: props.status, cls: 'bg-gray-100 text-gray-600' }
})
</script>
