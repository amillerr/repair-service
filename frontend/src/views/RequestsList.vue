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
              <div v-else class="requests-list__assign-wrap">
                <button
                  type="button"
                  class="requests-list__assign"
                  :class="{ 'requests-list__assign--open': assignDropdown === row.id }"
                  @click.stop="toggleAssignDropdown(row.id)"
                >
                  <img src="/icon-user.svg" alt="" />
                  Назначить мастера
                </button>
                <div
                  v-if="assignDropdown === row.id"
                  class="requests-list__dropdown"
                  @click.stop
                >
                  <button
                    v-for="m in masters"
                    :key="m.id"
                    type="button"
                    class="requests-list__dropdown-item"
                    @click="doAssign(row, m)"
                  >
                    {{ m.name }}
                  </button>
                </div>
              </div>
            </td>
            <td class="requests-list__td-actions">
              <div class="requests-list__menu-wrap">
                <button
                  type="button"
                  class="requests-list__btn-dots"
                  aria-label="Меню"
                  @click.stop="menuOpenId = menuOpenId === row.id ? null : row.id"
                >
                  <img src="/icon-dots.svg" alt="" />
                </button>
                <div
                  v-if="menuOpenId === row.id"
                  class="requests-list__dropdown requests-list__dropdown--menu"
                  @click.stop
                >
                  <button
                    v-if="!['done', 'canceled'].includes(row.statusType)"
                    type="button"
                    class="requests-list__dropdown-item requests-list__dropdown-item--danger"
                    @click="doCancel(row)"
                  >
                    Отменить заявку
                  </button>
                </div>
              </div>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
  getDispatcherRequests,
  getMasters,
  assignMaster,
  cancelRequest,
} from '../api/requests'

const activeTab = ref('all')
const loading = ref(false)
const masters = ref([])
const assignDropdown = ref(null)
const menuOpenId = ref(null)

const tabs = [
  { id: 'all', label: 'Все' },
  { id: 'new', label: 'Новые' },
  { id: 'progress', label: 'В работе' },
  { id: 'done', label: 'Выполненные' },
]

// Фильтр: вкладка -> параметр API (Laravel: new, assigned, in_progress, done, canceled)
const STATUS_MAP = {
  all: null,
  new: 'new',
  progress: 'in_progress',
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
  const apiStatus = STATUS_MAP[activeTab.value]
  let list = apiStatus
    ? requests.value.filter((r) => r.statusType === (apiStatus === 'in_progress' ? 'progress' : apiStatus))
    : requests.value
  return [...list].sort((a, b) => (Number(a.id) || 0) - (Number(b.id) || 0))
})

const totalCount = computed(() => requests.value.length)

const rangeStart = computed(() =>
  filteredRequests.value.length ? 1 : 0
)
const rangeEnd = computed(() => filteredRequests.value.length)

const toStatusType = (s) => (s === 'in_progress' ? 'progress' : s ?? '')

const POLL_INTERVAL_MS = 15_000

async function fetchRequests(showLoading = false) {
  try {
    if (showLoading) loading.value = true
    const [reqData, mastersData] = await Promise.all([
      getDispatcherRequests({}),
      getMasters(),
    ])
    if (mastersData?.data?.length) {
      masters.value = mastersData.data
    }
    if (reqData?.data?.length) {
      requests.value = reqData.data.map((r) => ({
        id: r.id,
        clientName: r.client_name ?? r.clientName,
        initials: (r.client_name ?? '??').split(' ').map((s) => s[0]).join('').slice(0, 2).toUpperCase(),
        avatarColor: 'gray',
        phone: r.phone ?? '',
        address: r.address ?? '',
        status: r.status_label ?? r.status,
        statusType: toStatusType(r.status),
        master: r.master_name ?? r.master,
      }))
    }
  } catch {
    // молча при ошибке
  } finally {
    loading.value = false
  }
}

function toggleAssignDropdown(rowId) {
  assignDropdown.value = assignDropdown.value === rowId ? null : rowId
  if (assignDropdown.value) menuOpenId.value = null
}

async function doAssign(row, master) {
  try {
    await assignMaster(row.id, master.id)
    row.master = master.name
    row.statusType = 'assigned'
    row.status = 'Назначена'
  } catch {
    // ошибка уже показана axios interceptor
  }
  assignDropdown.value = null
}

async function doCancel(row) {
  try {
    await cancelRequest(row.id)
    row.statusType = 'canceled'
    row.status = 'Отменена'
    row.master = null
  } catch {
    // ошибка уже показана axios interceptor
  }
  menuOpenId.value = null
}

function handleClickOutside() {
  assignDropdown.value = null
  menuOpenId.value = null
}

let pollTimer = null

onMounted(() => {
  fetchRequests(true)
  pollTimer = setInterval(() => fetchRequests(false), POLL_INTERVAL_MS)
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
  document.removeEventListener('click', handleClickOutside)
})
</script>
