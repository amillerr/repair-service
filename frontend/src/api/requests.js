import api from './axios'

export async function login(email, password) {
  const { data } = await api.post('/login', { email, password })
  return data
}

export async function getDispatcherRequests(params = {}) {
  const { data } = await api.get('/dispatcher/requests', { params })
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
