<template>
  <div class="masters-page">
    <div class="masters-page__header">
      <h1 class="masters-page__title">Мастера</h1>
      <p class="masters-page__subtitle">
        Список доступных мастеров для назначения на заявки
      </p>
    </div>

    <div class="masters-page__table-wrap">
      <table class="masters-page__table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Мастер</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="master in masters" :key="master.id">
            <td>
              <span class="masters-page__id">#{{ master.id }}</span>
            </td>
            <td>
              <div class="masters-page__master">
                <div class="masters-page__avatar">{{ getInitials(master.name) }}</div>
                <span class="masters-page__name">{{ master.name }}</span>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && !masters.length">
            <td colspan="2" class="masters-page__empty">
              Мастеров пока нет
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getMasters } from '../api/requests'

const masters = ref([])
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
    const data = await getMasters()
    if (data?.data?.length) {
      masters.value = data.data
    }
  } catch {
    masters.value = []
  } finally {
    loading.value = false
  }
})
</script>
