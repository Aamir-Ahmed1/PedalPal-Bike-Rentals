<script setup>
const props = defineProps({
  bike: { type: Object, required: true },
  bikeType: { type: String, required: true },
})

const emit = defineEmits(['rent'])

const fields = props.bikeType === 'beach'
  ? [
      { label: 'Color', key: 'color' },
      { label: 'Frame Size', key: 'frame_size' },
    ]
  : [
      { label: 'Brand', key: 'brand' },
      { label: 'Suspension', key: 'suspension_type' },
      { label: 'Frame', key: 'frame_material' },
      { label: 'Gears', key: 'gear_count' },
      { label: 'Terrain', key: 'terrain' },
      { label: 'Weight', key: 'weight_kg', suffix: ' kg' },
    ]

const priceKey = props.bikeType === 'beach' ? 'daily_rate' : 'daily_rate'

function formatPrice(val) {
  return `$${Number(val).toFixed(2)}`
}

function fieldValue(bike, key) {
  const val = bike[key]
  if (key === 'weight_kg') return `${val} kg`
  return val
}
</script>

<template>
  <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col">
    <div class="flex items-start justify-between mb-3">
      <h3 class="text-lg font-bold text-gray-800">{{ bike.model_name || bike.ModelName }}</h3>
      <span
        v-if="bike.brand"
        class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded"
      >
        {{ bike.brand }}
      </span>
    </div>

    <div :class="[
      'inline-block self-start px-3 py-1 rounded-full text-xs font-semibold mb-4',
      (bike.is_available ?? bike.IsAvailable)
        ? 'bg-green-100 text-green-700'
        : 'bg-red-100 text-red-600'
    ]">
      {{ (bike.is_available ?? bike.IsAvailable) ? 'Available' : 'Rented' }}
    </div>

    <div class="space-y-2 text-sm flex-1">
      <div
        v-for="f in fields"
        :key="f.key"
        class="flex justify-between"
      >
        <span class="text-gray-400">{{ f.label }}</span>
        <span class="text-gray-700 font-medium">
          {{ fieldValue(bike, f.key) }}{{ f.suffix ?? '' }}
        </span>
      </div>
    </div>

    <div class="text-2xl font-bold mt-4 mb-4" :class="bikeType === 'beach' ? 'text-brand-pink' : 'text-brand-blue'">
      {{ formatPrice(bike[priceKey]) }}<span class="text-sm font-normal text-gray-400">/day</span>
    </div>

    <button
      @click="emit('rent', bike)"
      :class="[
        'w-full py-3 rounded-lg font-semibold transition-all duration-200',
        bikeType === 'beach'
          ? 'text-white bg-gradient-to-r from-brand-pink to-brand-purple hover:shadow-lg hover:scale-[1.02] active:scale-95'
          : 'text-white bg-gradient-to-r from-brand-blue to-brand-cyan hover:shadow-lg hover:scale-[1.02] active:scale-95'
      ]"
    >
      Rent This Bike
    </button>
  </div>
</template>
