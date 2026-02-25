<template>
  <div class="create-request">
    <div class="create-request__header">
      <h1 class="create-request__title">Создать заявку</h1>
      <p class="create-request__subtitle">
        Заполните форму ниже для регистрации новой заявки на ремонт в системе.
      </p>
    </div>

    <div class="create-request__card">
      <div class="create-request__card-header">
        <div class="create-request__card-icon">
          <img src="/icon-document.svg" alt="" />
        </div>
        <div>
          <h2 class="create-request__card-title">Детали заявки</h2>
          <p class="create-request__card-desc">
            Укажите основную информацию о клиенте и проблеме
          </p>
        </div>
      </div>

      <form class="create-request__form" @submit.prevent="handleSubmit">
        <p v-if="error" class="create-request__error">{{ error }}</p>
        <div class="create-request__row">
          <div class="create-request__field">
            <label class="create-request__label">Имя клиента</label>
            <input
              v-model="form.clientName"
              type="text"
              class="create-request__input"
              placeholder="Например: Иван Петров"
            />
          </div>
          <div class="create-request__field">
            <label class="create-request__label">Телефон</label>
            <div class="create-request__input-wrap">
              <img src="/icon-phone.svg" alt="" class="create-request__input-icon" />
              <input
                v-model="form.phone"
                type="tel"
                class="create-request__input"
                placeholder="+7 (900) 000-00-00"
              />
            </div>
          </div>
        </div>

        <div class="create-request__field">
          <label class="create-request__label">Адрес</label>
          <div class="create-request__input-wrap">
            <img src="/icon-location.svg" alt="" class="create-request__input-icon" />
            <input
              v-model="form.address"
              type="text"
              class="create-request__input"
              placeholder="Улица, дом, квартира"
            />
          </div>
        </div>

        <div class="create-request__field">
          <label class="create-request__label">Описание проблемы</label>
          <textarea
            v-model="form.description"
            class="create-request__textarea"
            placeholder="Пожалуйста, опишите проблему подробно..."
            rows="5"
          ></textarea>
        </div>

        <div class="create-request__row">
          <div class="create-request__field">
            <label class="create-request__label">Тип услуги</label>
            <select v-model="form.serviceType" class="create-request__select">
              <option value="plumbing">Сантехника</option>
              <option value="electrical">Электрика</option>
              <option value="hvac">Отопление</option>
            </select>
          </div>
          <div class="create-request__field">
            <label class="create-request__label">Приоритет</label>
            <select v-model="form.priority" class="create-request__select">
              <option value="low">Низкий</option>
              <option value="normal">Обычный</option>
              <option value="high">Высокий</option>
            </select>
          </div>
          <div class="create-request__field">
            <label class="create-request__label">Дата визита</label>
            <input
              v-model="form.visitDate"
              type="text"
              class="create-request__input"
              placeholder="mm/dd/yyyy"
            />
          </div>
        </div>

        <div class="create-request__actions">
          <button type="button" class="create-request__btn-cancel" @click="$router.back()">
            Отмена
          </button>
          <button
            type="submit"
            class="create-request__btn-submit"
            :disabled="loading"
          >
            <img src="/icon-check.svg" alt="" class="create-request__btn-icon" />
            {{ loading ? 'Создание...' : 'Создать заявку' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { createRequest } from '../api/requests'

const router = useRouter()
const loading = ref(false)
const error = ref('')

const form = reactive({
  clientName: '',
  phone: '',
  address: '',
  description: '',
  serviceType: 'plumbing',
  priority: 'normal',
  visitDate: '',
})

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    await createRequest({
      client_name: form.clientName,
      phone: form.phone,
      address: form.address,
      problem_text: form.description,
    })
    router.push('/dispatcher/requests')
  } catch (err) {
    error.value =
      err.response?.data?.message ??
      Object.values(err.response?.data?.errors ?? {}).flat().join(' ') ??
      'Ошибка при создании заявки'
  } finally {
    loading.value = false
  }
}
</script>
