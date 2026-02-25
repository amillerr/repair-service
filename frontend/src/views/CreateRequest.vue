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
        <div class="create-request__row">
          <div
            class="create-request__field"
            :class="{ 'create-request__field--error': fieldErrors.client_name }"
          >
            <label class="create-request__label">Имя клиента</label>
            <input
              v-model="form.clientName"
              type="text"
              class="create-request__input"
              placeholder="Например: Иван Петров"
              @input="fieldErrors.client_name = ''"
            />
            <p v-if="fieldErrors.client_name" class="create-request__field-error">
              {{ fieldErrors.client_name }}
            </p>
          </div>
          <div
            class="create-request__field"
            :class="{ 'create-request__field--error': fieldErrors.phone }"
          >
            <label class="create-request__label">Телефон</label>
            <div class="create-request__input-wrap">
              <img src="/icon-phone.svg" alt="" class="create-request__input-icon" />
              <input
                v-model="form.phone"
                type="tel"
                class="create-request__input"
                placeholder="+7 (900) 000-00-00"
                maxlength="18"
                @input="onPhoneInput"
              />
            </div>
            <p v-if="fieldErrors.phone" class="create-request__field-error">
              {{ fieldErrors.phone }}
            </p>
          </div>
        </div>

        <div
          class="create-request__field"
          :class="{ 'create-request__field--error': fieldErrors.address }"
        >
          <label class="create-request__label">Адрес</label>
          <div class="create-request__input-wrap">
            <img src="/icon-location.svg" alt="" class="create-request__input-icon" />
            <input
              v-model="form.address"
              type="text"
              class="create-request__input"
              placeholder="Улица, дом, квартира"
              @input="fieldErrors.address = ''"
            />
          </div>
          <p v-if="fieldErrors.address" class="create-request__field-error">
            {{ fieldErrors.address }}
          </p>
        </div>

        <div
          class="create-request__field"
          :class="{ 'create-request__field--error': fieldErrors.problem_text }"
        >
          <label class="create-request__label">Описание проблемы</label>
          <textarea
            v-model="form.description"
            class="create-request__textarea"
            placeholder="Пожалуйста, опишите проблему подробно..."
            rows="5"
            @input="fieldErrors.problem_text = ''"
          ></textarea>
          <p v-if="fieldErrors.problem_text" class="create-request__field-error">
            {{ fieldErrors.problem_text }}
          </p>
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
import { useToast } from '../composables/useToast'

const router = useRouter()
const { add: toast } = useToast()
const loading = ref(false)
const fieldErrors = reactive({
  client_name: '',
  phone: '',
  address: '',
  problem_text: '',
})

const form = reactive({
  clientName: '',
  phone: '',
  address: '',
  description: '',
})

function onPhoneInput() {
  fieldErrors.phone = ''
  formatPhone()
}

function formatPhone() {
  let digits = form.phone.replace(/\D/g, '')
  if (digits.startsWith('8') && digits.length <= 1) {
    digits = '7'
  } else if (digits.startsWith('8')) {
    digits = '7' + digits.slice(1)
  } else if (!digits.startsWith('7')) {
    digits = '7' + digits
  }
  digits = digits.slice(0, 11)
  if (digits.length === 0) {
    form.phone = ''
    return
  }
  let formatted = '+7'
  if (digits.length > 1) {
    formatted += ' (' + digits.slice(1, 4)
    if (digits.length > 4) formatted += ') ' + digits.slice(4, 7)
    if (digits.length > 7) formatted += '-' + digits.slice(7, 9)
    if (digits.length > 9) formatted += '-' + digits.slice(9, 11)
  }
  form.phone = formatted
}

function clearFieldErrors() {
  fieldErrors.client_name = ''
  fieldErrors.phone = ''
  fieldErrors.address = ''
  fieldErrors.problem_text = ''
}

async function handleSubmit() {
  clearFieldErrors()
  loading.value = true
  try {
    await createRequest({
      client_name: form.clientName,
      phone: form.phone,
      address: form.address,
      problem_text: form.description,
    })
    toast('Заявка успешно создана', { type: 'success' })
    router.push('/dispatcher/requests')
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors && typeof errors === 'object') {
      Object.keys(fieldErrors).forEach((key) => {
        if (errors[key]?.length) {
          fieldErrors[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key]
        }
      })
    } else {
      fieldErrors.problem_text =
        err.response?.data?.message ?? 'Ошибка при создании заявки. Проверьте данные.'
    }
  } finally {
    loading.value = false
  }
}
</script>
