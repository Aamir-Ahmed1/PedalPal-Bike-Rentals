import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './style.css'

import HomeView from './views/HomeView.vue'
import BeachCruisersView from './views/BeachCruisersView.vue'
import MountainBikesView from './views/MountainBikesView.vue'

const routes = [
  { path: '/', name: 'home', component: HomeView, meta: { title: 'PedalPal — Bike Rentals' } },
  { path: '/beach-cruisers', name: 'beach', component: BeachCruisersView, meta: { title: 'Beach Cruisers' } },
  { path: '/mountain-bikes', name: 'mountain', component: MountainBikesView, meta: { title: 'Mountain Bikes' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.afterEach((to) => {
  document.title = to.meta.title ?? 'PedalPal'
})

createApp(App).use(router).mount('#app')
