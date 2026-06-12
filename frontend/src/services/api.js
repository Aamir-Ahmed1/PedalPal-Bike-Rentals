const BASE = '/api'

async function request(path, options = {}) {
  const res = await fetch(`${BASE}${path}`, {
    headers: { 'Content-Type': 'application/json' },
    ...options,
  })
  const data = await res.json()
  if (!res.ok) {
    throw new Error(data.message || `HTTP ${res.status}`)
  }
  return data
}

export const api = {
  beachCruisers: () => request('/beach-cruisers'),
  mountainBikes: () => request('/mountain-bikes'),
  rentBike: (bikeType, bikeId) =>
    request('/bikes/rent', {
      method: 'POST',
      body: JSON.stringify({ bikeType, bikeId }),
    }),
  accessories: (bikeType = '') =>
    request(`/accessories${bikeType ? `?bikeType=${bikeType}` : ''}`),
  orderAccessories: (items) =>
    request('/accessories/order', {
      method: 'POST',
      body: JSON.stringify(items),
    }),
  reset: () =>
    request('/reset', { method: 'POST' }),
}
