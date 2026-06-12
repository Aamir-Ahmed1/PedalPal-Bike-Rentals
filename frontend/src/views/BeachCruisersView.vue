<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../services/api.js'
import BikeCard from '../components/BikeCard.vue'
import AccessoryModal from '../components/AccessoryModal.vue'

const router = useRouter()
const bikes = ref([])
const loading = ref(true)
const error = ref('')
const showModal = ref(false)
const selectedBikeId = ref(null)

onMounted(fetchBikes)

async function fetchBikes() {
  loading.value = true
  error.value = ''
  try {
    bikes.value = await api.beachCruisers()
  } catch {
    error.value = 'Failed to load bikes. Make sure the PHP server is running on port 8080.'
  }
  loading.value = true
  loading.value = false
}

function handleRent(bike) {
  selectedBikeId.value = bike.id
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  selectedBikeId.value = null
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-brand-purple to-brand-pink">
    <div class="max-w-6xl mx-auto px-4 py-8">
      <div class="mb-8 flex items-center gap-4">
        <button
          @click="router.push('/')"
          class="text-white/70 hover:text-white transition-colors text-sm"
        >
          &larr; Back to PedalPal
        </button>
        <div class="flex-1 text-center">
          <h1 class="text-3xl sm:text-4xl font-extrabold text-white drop-shadow-md">
            🏖️ Beach Cruisers
          </h1>
          <p class="text-white/70 mt-1">Slow down. Feel the breeze. Avoid thinking about your inbox.</p>
        </div>
        <div class="w-20"></div>
      </div>

      <div v-if="loading" class="text-center py-20 text-white/70 text-lg">
        Loading bikes...
      </div>

      <div v-else-if="error" class="bg-white/90 rounded-xl p-8 text-center max-w-lg mx-auto">
        <p class="text-red-500">{{ error }}</p>
        <p class="text-gray-400 text-sm mt-2">Try: <code class="bg-gray-100 px-2 py-0.5 rounded">php -S localhost:8080 -t public</code></p>
      </div>

      <div v-else-if="bikes.length === 0" class="text-center py-20 text-white/70">
        No beach cruisers available right now.
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <BikeCard
          v-for="bike in bikes"
          :key="bike.id"
          :bike="bike"
          bike-type="beach"
          @rent="handleRent"
        />
      </div>
    </div>

    <AccessoryModal
      :show="showModal"
      bike-type="beach"
      :bike-id="selectedBikeId"
      @close="closeModal"
      @refresh="fetchBikes"
    />
  </div>
</template>
