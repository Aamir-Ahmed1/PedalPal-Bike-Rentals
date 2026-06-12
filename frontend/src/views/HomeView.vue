<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../services/api.js'

const router = useRouter()
const showToast = ref(false)
const toastMessage = ref('')

async function handleReset() {
  try {
    await api.reset()
    toastMessage.value = 'All data reset to defaults!'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  } catch {
    toastMessage.value = 'Reset failed. Please try again.'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-brand-purple to-brand-pink flex flex-col items-center px-4 py-12 sm:py-20">
    <header class="text-center mb-12 sm:mb-16">
      <button
        @click="handleReset"
        class="text-5xl sm:text-6xl mb-4 inline-block transition-transform hover:scale-110 active:scale-90 cursor-pointer"
        title="Reset all data to defaults"
      >
        🚲
      </button>
      <h1 class="text-4xl sm:text-5xl font-extrabold text-white drop-shadow-lg">
        PedalPal Bike Rentals
      </h1>
      <p class="mt-3 text-white/80 text-lg sm:text-xl">
        Choose your adventure. Pedals provided. Excuses are extra.
      </p>
    </header>

    <div class="flex flex-col sm:flex-row gap-6 sm:gap-8 w-full max-w-2xl">
      <div
        @click="router.push('/beach-cruisers')"
        class="group bg-white/95 backdrop-blur-sm rounded-2xl p-8 sm:p-10 text-center shadow-2xl cursor-pointer transition-all duration-300 hover:-translate-y-2 hover:shadow-3xl flex-1"
      >
        <div class="text-6xl mb-5">🏖️</div>
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Beach Cruisers</h2>
        <p class="text-gray-500 leading-relaxed mb-6">
          Laid-back frames, wide tires, and enough style to make the seagulls jealous.
        </p>
        <span class="inline-block px-8 py-3 rounded-full font-semibold text-white bg-gradient-to-r from-brand-pink to-brand-purple transition-all duration-200 group-hover:shadow-lg group-hover:scale-105">
          Browse Beach Cruisers
        </span>
      </div>

      <div
        @click="router.push('/mountain-bikes')"
        class="group bg-white/95 backdrop-blur-sm rounded-2xl p-8 sm:p-10 text-center shadow-2xl cursor-pointer transition-all duration-300 hover:-translate-y-2 hover:shadow-3xl flex-1"
      >
        <div class="text-6xl mb-5">⛰️</div>
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Mountain Bikes</h2>
        <p class="text-gray-500 leading-relaxed mb-6">
          Suspension forks, aggressive geometry, and gears for days.
        </p>
        <span class="inline-block px-8 py-3 rounded-full font-semibold text-white bg-gradient-to-r from-brand-blue to-brand-cyan transition-all duration-200 group-hover:shadow-lg group-hover:scale-105">
          Browse Mountain Bikes
        </span>
      </div>
    </div>

    <footer class="mt-16 text-white/50 text-sm text-center">
      PedalPal &copy; 2026. All bikes are real. All prices are competitive. All code is modern.
    </footer>

    <Teleport to="body">
      <div
        v-if="showToast"
        class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-gray-900/90 text-white px-6 py-3 rounded-lg shadow-2xl z-50 text-sm transition-all duration-300"
      >
        {{ toastMessage }}
      </div>
    </Teleport>
  </div>
</template>
