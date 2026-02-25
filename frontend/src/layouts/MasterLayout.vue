<template>
  <div class="app-layout">
    <aside class="app-sidebar app-sidebar--master">
      <div class="app-sidebar__brand">
        <div class="app-sidebar__logo">
          <img src="/logo.svg" alt="" class="app-sidebar__logo-icon" />
        </div>
        <span class="app-sidebar__title">Портал мастера</span>
      </div>
      <nav class="app-sidebar__nav">
        <RouterLink to="/master/tasks" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-create.svg" alt="" class="app-sidebar__icon" />
          Мои заявки
        </RouterLink>
        <RouterLink to="/master/history" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-report.svg" alt="" class="app-sidebar__icon" />
          История
        </RouterLink>
      </nav>
      <div class="app-sidebar__footer">
        <RouterLink to="/master/settings" class="app-sidebar__link" active-class="app-sidebar__link--active">
          <img src="/icon-settings.svg" alt="" class="app-sidebar__icon" />
          Настройки
        </RouterLink>
        <div class="app-sidebar__user">
          <div class="app-sidebar__user-avatar">И</div>
          <div class="app-sidebar__user-info">
            <span class="app-sidebar__user-name">Иван Мастеров</span>
            <span class="app-sidebar__user-role">Мастер</span>
          </div>
        </div>
      </div>
    </aside>
    <div class="app-main">
      <header class="app-header app-header--master">
        <div class="app-header__search-wide">
          <img src="/icon-search.svg" alt="" class="app-header__search-icon" />
          <input type="text" class="app-header__search-input" placeholder="Поиск моих заявок..." />
        </div>
        <div class="app-header__actions">
          <button type="button" class="app-header__btn-icon app-header__btn-icon--bell" aria-label="Уведомления">
            <img src="/icon-bell.svg" alt="" />
            <span class="app-header__badge"></span>
          </button>
        </div>
      </header>
      <main class="app-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>
