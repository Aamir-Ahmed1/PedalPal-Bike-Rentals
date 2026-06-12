<script setup>
import { ref, computed, watch } from 'vue'
import { api } from '../services/api.js'

const props = defineProps({
  show: Boolean,
  bikeType: String,
  bikeId: Number,
})

const emit = defineEmits(['close', 'refresh'])

const accessories = ref([])
const quantities = ref({})
const loading = ref(false)
const renting = ref(false)
const orderSuccess = ref(false)
const orderMessage = ref('')

const BUNDLE_IDS = [1, 3]

const subtotal = computed(() =>
  accessories.value.reduce((sum, acc) => {
    const qty = quantities.value[acc.id] ?? 0
    return sum + acc.unit_price * qty
  }, 0)
)

const hasBundleA = computed(() => (quantities.value[BUNDLE_IDS[0]] ?? 0) > 0)
const hasBundleB = computed(() => (quantities.value[BUNDLE_IDS[1]] ?? 0) > 0)
const bundleApplied = computed(() => hasBundleA.value && hasBundleB.value)
const discount = computed(() => bundleApplied.value ? round(subtotal.value * 0.1) : 0)
const total = computed(() => round(subtotal.value - discount.value))
const hasItems = computed(() => accessories.value.some(acc => (quantities.value[acc.id] ?? 0) > 0))

function round(n) { return Math.round(n * 100) / 100 }

function handleOverlayClick() {
  if (!renting.value) {
    emit('close')
    emit('refresh')
  }
}

watch(() => props.show, async (val) => {
  if (!val) return
  loading.value = true
  orderSuccess.value = false
  quantities.value = {}
  try {
    const data = await api.accessories(props.bikeType)
    accessories.value = data
    data.forEach(acc => { quantities.value[acc.id] = 0 })
  } catch { accessories.value = [] }
  loading.value = false
})

function adjust(id, delta) {
  const acc = accessories.value.find(a => a.id === id)
  if (!acc) return
  const current = quantities.value[id] ?? 0
  const next = Math.max(0, Math.min(current + delta, acc.stock_count))
  quantities.value[id] = next
}

async function rentBike() {
  renting.value = true
  try {
    await api.rentBike(props.bikeType, props.bikeId)
    return true
  } catch {
    alert('Could not rent bike.')
    emit('close')
    emit('refresh')
    return false
  } finally {
    renting.value = false
  }
}

function skipOrder() {
  emit('close')
  emit('refresh')
}

async function submitOrder() {
  const ok = await rentBike()
  if (!ok) return

  const items = accessories.value
    .filter(acc => (quantities.value[acc.id] ?? 0) > 0)
    .map(acc => ({ AccessoryID: acc.id, Quantity: quantities.value[acc.id] }))

  if (items.length === 0) {
    emit('close')
    emit('refresh')
    return
  }

  try {
    const res = await api.orderAccessories(items)
    orderSuccess.value = true
    orderMessage.value = res.bundleDiscountApplied
      ? `Order placed! Total: $${res.totalPrice.toFixed(2)} (saved $${res.discountAmount.toFixed(2)})`
      : `Order placed! Total: $${res.totalPrice.toFixed(2)}`
    setTimeout(() => {
      emit('close')
      emit('refresh')
    }, 3000)
  } catch {
    alert('Order failed. Please try again.')
    emit('close')
    emit('refresh')
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="handleOverlayClick"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto p-6 sm:p-8 relative animate-fade-in">
        <button
          @click="handleOverlayClick"
          :disabled="renting"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl leading-none disabled:opacity-30"
        >
          &times;
        </button>

        <template v-if="orderSuccess">
          <div class="text-center py-8">
            <div class="text-5xl mb-4">🎉</div>
            <h3 class="text-xl font-bold text-green-600 mb-2">You're all set!</h3>
            <p class="text-gray-500">{{ orderMessage }}</p>
          </div>
        </template>

        <template v-else>
          <h2 class="text-2xl font-bold text-gray-800 mb-1">Add Accessories</h2>
          <p class="text-gray-400 text-sm mb-6">
            Add accessories for your ride?
          </p>

          <div
            v-if="bundleApplied"
            class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-5 text-sm text-amber-800"
          >
            🎉 <strong>Bundle Deal!</strong> Water Bottle + Bike Light = 10% off your entire order.
          </div>

          <div v-if="loading" class="text-center py-10 text-gray-400">
            Loading accessories...
          </div>

          <div v-else-if="accessories.length === 0" class="text-center py-10 text-gray-400">
            No accessories available.
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="acc in accessories"
              :key="acc.id"
              class="border border-gray-100 rounded-xl p-4 flex items-center gap-4"
            >
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-gray-800 text-sm">{{ acc.name }}</h4>
                <p class="text-xs text-gray-400 truncate">{{ acc.description }}</p>
                <span class="text-xs text-gray-300">
                  {{ acc.category }} &bull; In stock: {{ acc.stock_count }}
                </span>
              </div>
              <div class="text-brand-pink font-bold text-sm whitespace-nowrap">
                ${{ acc.unit_price.toFixed(2) }}
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="adjust(acc.id, -1)"
                  class="w-8 h-8 rounded-full border-2 border-brand-pink text-brand-pink font-bold hover:bg-brand-pink hover:text-white transition-colors"
                >
                  &minus;
                </button>
                <span class="w-6 text-center font-semibold text-gray-700">
                  {{ quantities[acc.id] ?? 0 }}
                </span>
                <button
                  @click="adjust(acc.id, 1)"
                  :disabled="(quantities[acc.id] ?? 0) >= acc.stock_count"
                  class="w-8 h-8 rounded-full border-2 border-brand-pink text-brand-pink font-bold hover:bg-brand-pink hover:text-white transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                >
                  +
                </button>
              </div>
            </div>
          </div>

          <div v-if="accessories.length > 0" class="border-t border-gray-100 mt-6 pt-5">
            <div class="flex justify-between text-sm text-gray-500">
              <span>Subtotal</span>
              <span>${{ subtotal.toFixed(2) }}</span>
            </div>
            <div v-if="bundleApplied" class="flex justify-between text-sm text-green-600">
              <span>Bundle Discount (10%)</span>
              <span>-${{ discount.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold text-gray-800 mt-2 pt-2 border-t border-gray-100">
              <span>Total</span>
              <span v-if="bikeType === 'beach'" class="text-brand-pink">${{ total.toFixed(2) }}</span>
              <span v-else class="text-brand-blue">${{ total.toFixed(2) }}</span>
            </div>
          </div>

          <button
            v-if="accessories.length > 0"
            :disabled="!hasItems || renting"
            @click="submitOrder"
            :class="[
              'w-full mt-5 py-3 rounded-xl font-bold text-white transition-all duration-200',
              hasItems && !renting
                ? bikeType === 'beach'
                  ? 'bg-gradient-to-r from-brand-pink to-brand-purple hover:shadow-lg hover:scale-[1.02]'
                  : 'bg-gradient-to-r from-brand-blue to-brand-cyan hover:shadow-lg hover:scale-[1.02]'
                : 'bg-gray-200 text-gray-400 cursor-not-allowed'
            ]"
          >
            {{ renting ? 'Renting bike...' : 'Confirm Order' }}
          </button>

          <button
            @click="skipOrder"
            :disabled="renting"
            class="w-full mt-3 py-2.5 rounded-xl border-2 border-gray-200 text-gray-400 font-medium hover:border-gray-300 hover:text-gray-500 transition-colors disabled:opacity-50"
          >
            No thanks, just the bike
          </button>
        </template>
      </div>
    </div>
  </Teleport>
</template>
