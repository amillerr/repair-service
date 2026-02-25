<template>
  <div class="app-layout">
    <aside class="app-sidebar app-sidebar--dispatcher">
      <div class="app-sidebar__brand">
        <div class="app-sidebar__logo">
          <img src="/logo.svg" alt="" class="app-sidebar__logo-icon" />
        </div>
        <span class="app-sidebar__title">Портал диспетчера</span>
      </div>
      <nav class="app-sidebar__nav">
        <RouterLink to="/dispatcher/dashboard" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-dashboard.svg" alt="" class="app-sidebar__icon" />
          Дашборд
        </RouterLink>
        <RouterLink to="/dispatcher/requests" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-create.svg" alt="" class="app-sidebar__icon" />
          Заявки на сервис
        </RouterLink>
        <RouterLink to="/dispatcher/masters" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-user.svg" alt="" class="app-sidebar__icon" />
          Мастера
        </RouterLink>
        <RouterLink to="/dispatcher/clients" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-clients.svg" alt="" class="app-sidebar__icon" />
          Клиенты
        </RouterLink>
        <RouterLink to="/dispatcher/reports" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-report.svg" alt="" class="app-sidebar__icon" />
          Отчеты
        </RouterLink>
      </nav>
      <div class="app-sidebar__footer">
        <RouterLink to="/dispatcher/settings" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-settings.svg" alt="" class="app-sidebar__icon" />
          Настройки
        </RouterLink>
        <div class="app-sidebar__user">
          <div class="app-sidebar__user-avatar">А</div>
          <div class="app-sidebar__user-info">
            <span class="app-sidebar__user-name">Александр Иванов</span>
            <span class="app-sidebar__user-role">Старший диспетчер</span>
          </div>
        </div>
      </div>
    </aside>
    <div class="app-main">
      <header class="app-header app-header--dispatcher">
        <div class="app-header__search-wide">
          <img src="/icon-search.svg" alt="" class="app-header__search-icon" />
          <input type="text" class="app-header__search-input" placeholder="Поиск заявок..." />
        </div>
        <div class="app-header__actions">
          <button type="button" class="app-header__btn-icon app-header__btn-icon--bell" aria-label="Уведомления">
            <img src="/icon-bell.svg" alt="" />
            <span class="app-header__badge"></span>
          </button>
          <RouterLink to="/app/requests/create" class="app-header__btn-primary">
            <img src="/icon-plus.svg" alt="" class="app-header__btn-icon-svg" />
            Новая заявка
          </RouterLink>
        </div>
      </header>
      <main class="app-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>
