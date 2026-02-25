import api from './axios'

export async function login(name) {
  const { data } = await api.post('/login', { name })
  return data
}

export async function logout() {
  try {
    await api.post('/logout')
  } finally {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    window.location.href = '/login'
  }
}

export async function getDispatcherRequests(params = {}) {
  const { data } = await api.get('/dispatcher/requests', { params })
  return data
}

export async function getMasters() {
  const { data } = await api.get('/dispatcher/masters')
  return data
}

export async function getClients() {
  const { data } = await api.get('/dispatcher/clients')
  return data
}

export async function getMasterRequests(params = {}) {
  const { data } = await api.get('/master/requests', { params })
  return data
}

export async function createRequest(payload) {
  const { data } = await api.post('/requests', payload)
  return data
}

export async function cancelRequest(requestId) {
  const { data } = await api.post(`/dispatcher/requests/${requestId}/cancel`)
  return data
}

export async function assignMaster(requestId, masterId) {
  const { data } = await api.post(
    `/dispatcher/requests/${requestId}/assign`,
    { master_id: masterId }
  )
  return data
}

export async function takeRequest(requestId) {
  const { data } = await api.post(`/master/requests/${requestId}/take`)
  return data
}

export async function completeRequest(requestId, payload = {}) {
  const { data } = await api.post(
    `/master/requests/${requestId}/complete`,
    payload
  )
  return data
}
