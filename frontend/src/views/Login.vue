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
          <!-- User select -->
          <div class="login-page__field">
            <label class="login-page__label">Пользователь</label>
            <div class="login-page__input-wrap">
              <img src="/icon-user.svg" alt="" class="login-page__input-icon" />
              <select v-model="selectedUser" class="login-page__input login-page__select">
                <option value="Dispatcher">Диспетчер</option>
                <option value="Алексей Смирнов">Алексей Смирнов (мастер)</option>
                <option value="Дмитрий Кузнецов">Дмитрий Кузнецов (мастер)</option>
              </select>
            </div>
          </div>

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
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { login as apiLogin } from '../api/requests'

const router = useRouter()
const route = useRoute()
const selectedUser = ref('Dispatcher')
const loading = ref(false)
const error = ref('')

const role = computed(() =>
  selectedUser.value === 'Dispatcher' ? 'dispatcher' : 'master'
)

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    const data = await apiLogin(selectedUser.value)
    if (data.token) {
      localStorage.setItem('auth_token', data.token)
    }
    if (data.user) {
      localStorage.setItem('auth_user', JSON.stringify(data.user))
    }
    const userRole = data.user?.role ?? role.value
    const redirect = route.query.redirect

    if (redirect && redirect.startsWith('/')) {
      const isDispatcherPath = redirect.startsWith('/dispatcher')
      const isMasterPath = redirect.startsWith('/master')
      if (userRole === 'master' && isDispatcherPath) {
        router.push('/master/tasks')
      } else if (userRole === 'dispatcher' && isMasterPath) {
        router.push('/dispatcher/requests')
      } else {
        router.push(redirect)
      }
    } else if (userRole === 'master') {
      router.push('/master/tasks')
    } else {
      router.push('/dispatcher/requests')
    }
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Ошибка входа. Проверьте данные.'
  } finally {
    loading.value = false
  }
}
</script>
