<template>
  <div class="login-page">
    <div class="login-page__container">
      <!-- Header -->
      <div class="login-page__header">
        <RouterLink to="/" class="login-page__logo">
          <img src="/logo.svg" alt="" class="login-page__logo-icon" />
        </RouterLink>
        <h1 class="login-page__title">Ремонтная служба</h1>
        <p class="login-page__subtitle">Управление заявками на ремонт</p>
      </div>

      <!-- Form card -->
      <div class="login-page__card">
        <form class="login-page__form" @submit.prevent="handleSubmit">
          <p v-if="error" class="login-page__error">{{ error }}</p>
          <!-- Role selector -->
          <div class="login-page__field">
            <label class="login-page__label">Выберите роль</label>
            <div class="login-page__role-tabs">
              <button
                type="button"
                class="login-page__role-tab"
                :class="{ 'login-page__role-tab--active': role === 'dispatcher' }"
                @click="role = 'dispatcher'"
              >
                Диспетчер
              </button>
              <button
                type="button"
                class="login-page__role-tab"
                :class="{ 'login-page__role-tab--active': role === 'master' }"
                @click="role = 'master'"
              >
                Мастер
              </button>
            </div>
          </div>

          <!-- Email -->
          <div class="login-page__field">
            <label class="login-page__label">Электронная почта</label>
            <div class="login-page__input-wrap">
              <img src="/icon-email.svg" alt="" class="login-page__input-icon" />
              <input
                v-model="email"
                type="email"
                class="login-page__input"
                placeholder="name@company.ru"
                autocomplete="email"
              />
            </div>
          </div>

          <!-- Password -->
          <div class="login-page__field">
            <div class="login-page__label-row">
              <label class="login-page__label">Пароль</label>
              <a href="#" class="login-page__link">Забыли пароль?</a>
            </div>
            <div class="login-page__input-wrap">
              <img src="/icon-lock.svg" alt="" class="login-page__input-icon" />
              <input
                v-model="password"
                type="password"
                class="login-page__input"
                placeholder="••••••••"
                autocomplete="current-password"
              />
            </div>
          </div>

          <!-- Remember me -->
          <label class="login-page__checkbox-wrap">
            <input v-model="remember" type="checkbox" class="login-page__checkbox" />
            <span class="login-page__checkbox-label">Запомнить меня</span>
          </label>

          <!-- Submit -->
          <button type="submit" class="login-page__submit" :disabled="loading">
            {{ loading ? 'Вход...' : 'Войти' }}
          </button>
        </form>

        <!-- No account -->
        <div class="login-page__divider"></div>
        <p class="login-page__register">
          Нет аккаунта? <a href="#" class="login-page__link">Запросить доступ</a>
        </p>
      </div>

      <!-- Footer links -->
      <div class="login-page__footer-links">
        <a href="#" class="login-page__footer-link">Конфиденциальность</a>
        <a href="#" class="login-page__footer-link">Условия</a>
        <a href="#" class="login-page__footer-link">Поддержка</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login as apiLogin } from '../api/requests'

const router = useRouter()
const role = ref('dispatcher')
const email = ref('')
const password = ref('')
const remember = ref(false)
const loading = ref(false)
const error = ref('')

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    const data = await apiLogin(email.value, password.value)
    if (data.token) {
      localStorage.setItem('auth_token', data.token)
    }
    if (role.value === 'master') {
      router.push('/master/tasks')
    } else {
      router.push('/dispatcher/requests')
    }
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Ошибка входа. Проверьте данные.'
    if (!err.response) {
      router.push(role.value === 'master' ? '/master/tasks' : '/dispatcher/requests')
    }
  } finally {
    loading.value = false
  }
}
</script>
