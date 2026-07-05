<template>
  <CustomerLayout title="Penilaian Menu" subtitle="Bantu kami menjadi lebih baik">
    <main class="max-w-2xl mx-auto px-4 py-10">
      <div class="bg-white rounded-2xl shadow-md p-8">
        <div class="text-center mb-8">
          <div class="text-5xl mb-3">📝</div>
          <h2 class="text-2xl font-bold text-gray-800 mb-2">Penilaian Menu</h2>
          <p class="text-gray-500 text-sm">
            Beri nilai untuk setiap menu yang baru saja Anda pesan, berdasarkan pengalaman Anda.
          </p>
        </div>

        <div v-if="flash.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
          {{ flash.success }}
        </div>

        <div v-if="criteria.length === 0 || items.length === 0" class="text-center text-gray-400 py-10">
          Belum ada kriteria penilaian yang tersedia saat ini.
        </div>

        <div v-else class="space-y-8">
          <div v-for="item in items" :key="item.menu_id" class="border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-5">
              <div class="w-12 h-12 rounded-xl overflow-hidden bg-amber-100 flex items-center justify-center text-xl flex-shrink-0">
                <img v-if="item.image_path" :src="asset('/storage/' + item.image_path)" :alt="item.name" class="w-full h-full object-cover" />
                <span v-else>🍜</span>
              </div>
              <h3 class="font-bold text-gray-800">{{ item.name }}</h3>
            </div>

            <div class="space-y-5">
              <CriterionScaleInput
                v-for="c in criteria"
                :key="c.id"
                :criterion="c"
                v-model="answers[item.menu_id][c.id]"
              />
            </div>
          </div>
        </div>

        <button
          v-if="items.length && criteria.length"
          @click="submitAnswers"
          :disabled="submitting || !hasAnyAnswer"
          class="w-full mt-8 bg-amber-700 hover:bg-amber-600 disabled:opacity-50 text-white font-bold py-3 rounded-xl transition-colors"
        >
          {{ submitting ? 'Mengirim...' : (alreadyAnswered ? 'Perbarui Penilaian' : 'Kirim Penilaian') }}
        </button>

        <Link
          :href="table?.qr_token ? route('order.enter', { qr_token: table.qr_token }) : '/'"
          class="w-full block text-center mt-4 text-sm text-gray-500 hover:text-amber-700 font-medium"
        >
          ← Kembali ke Menu
        </Link>
      </div>
    </main>
  </CustomerLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import CriterionScaleInput from '@/Components/CriterionScaleInput.vue'
import { asset } from '@/utils/asset'

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  items: {
    type: Array,
    default: () => [],
  },
  criteria: {
    type: Array,
    default: () => [],
  },
  answers: {
    type: Object,
    default: () => ({}),
  },
  table: {
    type: Object,
    default: null,
  },
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const submitting = ref(false)

const alreadyAnswered = computed(() => Object.keys(props.answers ?? {}).length > 0)

// Local editable copy, pre-filled from any answers already on record.
const answers = reactive({})
props.items.forEach((item) => {
  answers[item.menu_id] = {}
  props.criteria.forEach((c) => {
    const existing = props.answers?.[item.menu_id]?.[c.id]
    answers[item.menu_id][c.id] = existing !== undefined && existing !== null ? Number(existing) : null
  })
})

const hasAnyAnswer = computed(() =>
  Object.values(answers).some((criteriaAnswers) => Object.values(criteriaAnswers).some((v) => v !== null))
)

function submitAnswers() {
  const payload = []
  Object.entries(answers).forEach(([menuId, criteriaAnswers]) => {
    Object.entries(criteriaAnswers).forEach(([criterionId, score]) => {
      if (score !== null) {
        payload.push({ menu_id: Number(menuId), criterion_id: Number(criterionId), score })
      }
    })
  })

  submitting.value = true
  router.post(route('orders.review.store', props.order.id), { answers: payload }, {
    preserveScroll: true,
    onFinish: () => { submitting.value = false },
  })
}
</script>
