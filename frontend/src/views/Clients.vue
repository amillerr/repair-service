<template>
  <div class="clients-page">
    <div class="clients-page__header">
      <h1 class="clients-page__title">Клиенты</h1>
      <p class="clients-page__subtitle">
        Список клиентов из заявок
      </p>
    </div>

    <div class="clients-page__table-wrap">
      <table class="clients-page__table">
        <thead>
          <tr>
            <th>Клиент</th>
            <th>Телефон</th>
            <th>Адрес</th>
            <th>Заявок</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(client, idx) in clients" :key="idx">
            <td>
              <div class="clients-page__client">
                <div class="clients-page__avatar">{{ getInitials(client.client_name) }}</div>
                <span class="clients-page__name">{{ client.client_name }}</span>
              </div>
            </td>
            <td>{{ client.phone }}</td>
            <td>{{ client.address }}</td>
            <td>
              <span class="clients-page__count">{{ client.requests_count }}</span>
            </td>
          </tr>
          <tr v-if="!loading && !clients.length">
            <td colspan="4" class="clients-page__empty">
              Клиентов пока нет
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getClients } from '../api/requests'

const clients = ref([])
const loading = ref(false)

function getInitials(name) {
  return (name ?? '??')
    .split(' ')
    .map((s) => s[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
}

onMounted(async () => {
  try {
    loading.value = true
    const data = await getClients()
    if (data?.data?.length) {
      clients.value = data.data
    }
  } catch {
    clients.value = []
  } finally {
    loading.value = false
  }
})
</script>
