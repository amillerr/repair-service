<template>
  <div class="app-layout">
    <aside class="app-sidebar">
      <div class="app-sidebar__brand">
        <div class="app-sidebar__logo">
          <img src="/logo.svg" alt="" class="app-sidebar__logo-icon" />
        </div>
        <span class="app-sidebar__title">Система управления</span>
      </div>
      <nav class="app-sidebar__nav">
        <RouterLink to="/app/dashboard" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-dashboard.svg" alt="" class="app-sidebar__icon" />
          Панель управления
        </RouterLink>
        <RouterLink to="/app/requests/create" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-create.svg" alt="" class="app-sidebar__icon" />
          Создать заявку
        </RouterLink>
        <RouterLink to="/app/clients" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-clients.svg" alt="" class="app-sidebar__icon" />
          Клиенты
        </RouterLink>
        <div class="app-sidebar__divider"></div>
        <RouterLink to="/app/settings" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-settings.svg" alt="" class="app-sidebar__icon" />
          Настройки
        </RouterLink>
      </nav>
    </aside>
    <div class="app-main">
      <header class="app-header">
        <div class="app-header__breadcrumb">
          <span class="app-header__slash">/</span>
          <span class="app-header__page">{{ pageTitle }}</span>
        </div>
        <div class="app-header__right">
          <div class="app-header__search">
            <img src="/icon-search.svg" alt="" class="app-header__search-icon" />
            <input type="text" class="app-header__search-input" placeholder="Поиск заявок..." />
          </div>
          <div class="app-header__divider"></div>
          <button type="button" class="app-header__btn-icon" aria-label="Уведомления">
            <img src="/icon-bell.svg" alt="" />
          </button>
          <div class="app-header__user">
            <div class="app-header__user-info">
              <span class="app-header__user-name">Александр Иванов</span>
              <span class="app-header__user-role">Диспетчер</span>
            </div>
            <div class="app-header__avatar"></div>
          </div>
        </div>
      </header>
      <main class="app-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const pageTitle = computed(() => route.meta?.title ?? 'Создать заявку')
</script>
