<template>
  <div class="master-panel">
    <div class="master-panel__header">
      <h1 class="master-panel__title">Мои заявки</h1>
      <p class="master-panel__subtitle">
        Заявки, назначенные вам. Берите в работу и отмечайте выполнение.
      </p>
    </div>

    <div class="master-panel__toolbar">
      <div class="master-panel__tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          class="master-panel__tab"
          :class="{ 'master-panel__tab--active': activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <div class="master-panel__table-wrap">
      <table class="master-panel__table">
        <thead>
          <tr>
            <th>Клиент</th>
            <th>Телефон</th>
            <th>Адрес</th>
            <th>Тип услуги</th>
            <th>Статус</th>
            <th class="master-panel__th-actions">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in filteredTasks" :key="row.id">
            <td>
              <div class="master-panel__client">
                <div class="master-panel__avatar" :class="`master-panel__avatar--${row.avatarColor}`">
                  {{ row.initials }}
                </div>
                <div>
                  <div class="master-panel__client-name">{{ row.clientName }}</div>
                  <div class="master-panel__client-id">ID: {{ row.id }}</div>
                </div>
              </div>
            </td>
            <td>{{ row.phone }}</td>
            <td>{{ row.address }}</td>
            <td>{{ row.serviceType }}</td>
            <td>
              <span class="master-panel__status" :class="`master-panel__status--${row.statusType}`">
                {{ row.status }}
              </span>
            </td>
            <td class="master-panel__td-actions">
              <div class="master-panel__cell-actions">
                <template v-if="row.statusType === 'assigned'">
                  <button type="button" class="master-panel__btn-action" @click="takeInProgress(row)">
                    Взять в работу
                  </button>
                </template>
                <template v-else-if="row.statusType === 'progress'">
                  <button type="button" class="master-panel__btn-primary" @click="markDone(row)">
                    Завершить
                  </button>
                </template>
                <template v-else-if="row.statusType === 'done'">
                  <span class="master-panel__completed">Выполнена</span>
                </template>
                <button type="button" class="master-panel__btn-dots" aria-label="Меню">
                  <img src="/icon-dots.svg" alt="" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="master-panel__pagination">
      <span class="master-panel__pagination-text">
        Показано {{ rangeStart }}–{{ rangeEnd }} из {{ totalCount }} заявок
      </span>
      <div class="master-panel__pagination-btns">
        <button type="button" class="master-panel__pagination-btn" aria-label="Назад">&larr;</button>
        <button type="button" class="master-panel__pagination-btn" aria-label="Вперёд">&rarr;</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getMasterRequests, takeRequest, completeRequest } from '../api/requests'

const activeTab = ref('assigned')
const loading = ref(false)

const tabs = [
  { id: 'assigned', label: 'Назначенные' },
  { id: 'progress', label: 'В работе' },
  { id: 'done', label: 'Выполненные' },
]

const STATUS_MAP = {
  assigned: 'assigned',
  progress: 'progress',
  done: 'done',
}

const tasks = ref([
  {
    id: '#REQ-4828',
    clientName: 'Анна Сидорова',
    initials: 'АС',
    avatarColor: 'blue',
    phone: '+7 (900) 010-5678',
    address: 'пр. Мира, 45, Санкт-Петербург',
    serviceType: 'Сантехника',
    status: 'Назначена',
    statusType: 'assigned',
  },
  {
    id: '#REQ-4827',
    clientName: 'Руслан Белый',
    initials: 'РБ',
    avatarColor: 'gray',
    phone: '+7 (900) 010-9012',
    address: 'ул. Садовая, 78, Казань',
    serviceType: 'Электрика',
    status: 'В работе',
    statusType: 'progress',
  },
  {
    id: '#REQ-4826',
    clientName: 'Алиса Зеленая',
    initials: 'АЗ',
    avatarColor: 'gray',
    phone: '+7 (900) 010-3456',
    address: 'б-р Юности, 32, Новосибирск',
    serviceType: 'Отопление',
    status: 'Выполнена',
    statusType: 'done',
  },
])

const filteredTasks = computed(() => {
  const status = STATUS_MAP[activeTab.value]
  if (!status) return tasks.value
  return tasks.value.filter((t) => t.statusType === status)
})

const totalCount = computed(() => tasks.value.length)

const rangeStart = computed(() =>
  filteredTasks.value.length ? 1 : 0
)
const rangeEnd = computed(() => filteredTasks.value.length)

onMounted(async () => {
  try {
    loading.value = true
    const data = await getMasterRequests({ status: activeTab.value })
    if (data?.data?.length) {
      tasks.value = data.data.map((r) => ({
        id: r.id,
        clientName: r.client_name ?? r.clientName,
        initials: (r.client_name ?? '??').split(' ').map((s) => s[0]).join('').slice(0, 2).toUpperCase(),
        avatarColor: 'gray',
        phone: r.phone ?? '',
        address: r.address ?? '',
        serviceType: r.service_type ?? r.serviceType ?? '—',
        status: r.status_label ?? r.status,
        statusType: r.status ?? r.status_type,
      }))
    }
  } catch {
    // используем mock-данные
  } finally {
    loading.value = false
  }
})

async function takeInProgress(row) {
  try {
    await takeRequest(row.id)
    row.status = 'В работе'
    row.statusType = 'progress'
  } catch {
    row.status = 'В работе'
    row.statusType = 'progress'
  }
}

async function markDone(row) {
  try {
    await completeRequest(row.id)
    row.status = 'Выполнена'
    row.statusType = 'done'
  } catch {
    row.status = 'Выполнена'
    row.statusType = 'done'
  }
}
</script>
