<template>
  <div class="requests-list">
    <div class="requests-list__header">
      <h1 class="requests-list__title">Панель диспетчера</h1>
      <p class="requests-list__subtitle">
        Обзор всех активных и архивных заявок на ремонт.
      </p>
    </div>

    <div class="requests-list__toolbar">
      <div class="requests-list__tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          class="requests-list__tab"
          :class="{ 'requests-list__tab--active': activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>
      <div class="requests-list__actions">
        <button type="button" class="requests-list__btn-secondary">
          <img src="/icon-filter.svg" alt="" />
          Фильтры
        </button>
        <button type="button" class="requests-list__btn-secondary">
          <img src="/icon-export.svg" alt="" />
          Экспорт
        </button>
      </div>
    </div>

    <div class="requests-list__table-wrap">
      <table class="requests-list__table">
        <thead>
          <tr>
            <th>Клиент</th>
            <th>Телефон</th>
            <th>Адрес</th>
            <th>Статус</th>
            <th>Мастер</th>
            <th class="requests-list__th-actions">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in filteredRequests" :key="row.id">
            <td>
              <div class="requests-list__client">
                <div class="requests-list__avatar" :class="`requests-list__avatar--${row.avatarColor}`">
                  {{ row.initials }}
                </div>
                <div>
                  <div class="requests-list__client-name">{{ row.clientName }}</div>
                  <div class="requests-list__client-id">ID: {{ row.id }}</div>
                </div>
              </div>
            </td>
            <td>{{ row.phone }}</td>
            <td>{{ row.address }}</td>
            <td>
              <span class="requests-list__status" :class="`requests-list__status--${row.statusType}`">
                {{ row.status }}
              </span>
            </td>
            <td>
              <template v-if="row.master">
                <div class="requests-list__master">
                  <div class="requests-list__master-avatar"></div>
                  {{ row.master }}
                </div>
              </template>
              <template v-else-if="row.statusType === 'canceled'">
                <span class="requests-list__not-assigned">Не назначен</span>
              </template>
              <button v-else type="button" class="requests-list__assign">
                <img src="/icon-user.svg" alt="" />
                Назначить мастера
              </button>
            </td>
            <td class="requests-list__td-actions">
              <button type="button" class="requests-list__btn-dots" aria-label="Меню">
                <img src="/icon-dots.svg" alt="" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="requests-list__pagination">
      <span class="requests-list__pagination-text">
        Показано {{ rangeStart }}-{{ rangeEnd }} из {{ totalCount }} результатов
      </span>
      <div class="requests-list__pagination-btns">
        <button type="button" class="requests-list__pagination-btn" aria-label="Назад">&larr;</button>
        <button type="button" class="requests-list__pagination-btn" aria-label="Вперёд">&rarr;</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getDispatcherRequests } from '../api/requests'

const activeTab = ref('all')
const loading = ref(false)

const tabs = [
  { id: 'all', label: 'Все' },
  { id: 'new', label: 'Новые' },
  { id: 'progress', label: 'В работе' },
  { id: 'done', label: 'Выполненные' },
]

const STATUS_MAP = {
  all: null,
  new: 'new',
  progress: 'progress',
  done: 'done',
}

const requests = ref([
  {
    id: '#REQ-4829',
    clientName: 'Иван Петров',
    initials: 'ИП',
    avatarColor: 'gray',
    phone: '+7 (900) 010-1234',
    address: 'ул. Ленина, 123, Москва',
    status: 'Новая',
    statusType: 'new',
    master: null,
  },
  {
    id: '#REQ-4828',
    clientName: 'Анна Сидорова',
    initials: 'АС',
    avatarColor: 'blue',
    phone: '+7 (900) 010-5678',
    address: 'пр. Мира, 45, Санкт-Петербург',
    status: 'Назначена',
    statusType: 'assigned',
    master: 'Мастер Вилсон',
  },
  {
    id: '#REQ-4827',
    clientName: 'Руслан Белый',
    initials: 'РБ',
    avatarColor: 'gray',
    phone: '+7 (900) 010-9012',
    address: 'ул. Садовая, 78, Казань',
    status: 'В работе',
    statusType: 'progress',
    master: 'Мастер Ли',
  },
  {
    id: '#REQ-4826',
    clientName: 'Алиса Зеленая',
    initials: 'АЗ',
    avatarColor: 'gray',
    phone: '+7 (900) 010-3456',
    address: 'б-р Юности, 32, Новосибирск',
    status: 'Выполнена',
    statusType: 'done',
    master: 'Мастер Дэвис',
  },
  {
    id: '#REQ-4825',
    clientName: 'Кирилл Белов',
    initials: 'КБ',
    avatarColor: 'gray',
    phone: '+7 (900) 010-7890',
    address: 'ул. Кедровая, 65, Екатеринбург',
    status: 'Отменена',
    statusType: 'canceled',
    master: null,
  },
])

const filteredRequests = computed(() => {
  const status = STATUS_MAP[activeTab.value]
  if (!status) return requests.value
  return requests.value.filter((r) => r.statusType === status)
})

const totalCount = computed(() => requests.value.length)

const rangeStart = computed(() =>
  filteredRequests.value.length ? 1 : 0
)
const rangeEnd = computed(() => filteredRequests.value.length)

onMounted(async () => {
  try {
    loading.value = true
    const data = await getDispatcherRequests({ status: activeTab.value })
    if (data?.data?.length) {
      requests.value = data.data.map((r) => ({
        id: r.id,
        clientName: r.client_name ?? r.clientName,
        initials: (r.client_name ?? '??').split(' ').map((s) => s[0]).join('').slice(0, 2).toUpperCase(),
        avatarColor: 'gray',
        phone: r.phone ?? '',
        address: r.address ?? '',
        status: r.status_label ?? r.status,
        statusType: r.status ?? r.status_type,
        master: r.master_name ?? r.master,
      }))
    }
  } catch {
    // используем mock-данные
  } finally {
    loading.value = false
  }
})
</script>
