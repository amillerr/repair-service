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
            <th>Описание проблемы</th>
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
            <td class="master-panel__problem-cell">{{ row.problemText }}</td>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { getMasterRequests, takeRequest, completeRequest } from '../api/requests'
import { useToast } from '../composables/useToast'

const activeTab = ref('assigned')
const loading = ref(false)
const { add: toast } = useToast()
const previousTaskIds = ref(new Set())
const hasInitiallyLoaded = ref(false)

const tabs = [
  { id: 'assigned', label: 'Назначенные' },
  { id: 'progress', label: 'В работе' },
  { id: 'done', label: 'Выполненные' },
]

// Фильтр: вкладка -> параметр API (Laravel: assigned, in_progress, done)
const STATUS_MAP = {
  assigned: 'assigned',
  progress: 'in_progress',
  done: 'done',
}

const tasks = ref([])

const toStatusType = (s) => (s === 'in_progress' ? 'progress' : s ?? '')

const filteredTasks = computed(() => {
  const apiStatus = STATUS_MAP[activeTab.value]
  if (!apiStatus) return tasks.value
  const statusType = apiStatus === 'in_progress' ? 'progress' : apiStatus
  return tasks.value.filter((t) => t.statusType === statusType)
})

const totalCount = computed(() => tasks.value.length)

const rangeStart = computed(() =>
  filteredTasks.value.length ? 1 : 0
)
const rangeEnd = computed(() => filteredTasks.value.length)

const POLL_INTERVAL_MS = 15_000

async function fetchTasks(showLoading = false) {
  try {
    if (showLoading) loading.value = true
    const data = await getMasterRequests({})
    if (data?.data?.length) {
      const newTasks = data.data.map((r) => ({
        id: r.id,
        clientName: r.client_name ?? r.clientName,
        initials: (r.client_name ?? '??').split(' ').map((s) => s[0]).join('').slice(0, 2).toUpperCase(),
        avatarColor: 'gray',
        phone: r.phone ?? '',
        address: r.address ?? '',
        problemText: r.problem_text ?? r.problemText ?? '—',
        status: r.status_label ?? r.status,
        statusType: toStatusType(r.status),
      }))
      const newIds = new Set(newTasks.map((t) => t.id))
      const prevIds = previousTaskIds.value
      if (hasInitiallyLoaded.value) {
        const added = newTasks.filter((t) => !prevIds.has(t.id))
        if (added.length > 0) {
          toast(added.length === 1 ? 'Назначена новая заявка' : `Назначено заявок: ${added.length}`, {
            type: 'info',
          })
        }
      }
      hasInitiallyLoaded.value = true
      previousTaskIds.value = newIds
      tasks.value = newTasks
    }
  } catch {
    // молча при ошибке (например, при потере сети)
  } finally {
    loading.value = false
  }
}

let pollTimer = null

onMounted(() => {
  fetchTasks(true)
  pollTimer = setInterval(() => fetchTasks(false), POLL_INTERVAL_MS)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
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
