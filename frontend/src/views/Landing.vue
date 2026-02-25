<template>
  <div class="landing">
    <header class="header">
      <div class="header__inner">
        <RouterLink to="/" class="header__logo">
          <img :src="assets.logo" alt="" class="header__logo-icon" />
          <span class="header__logo-text">Ремонтная служба</span>
        </RouterLink>
        <nav class="header__nav">
          <a href="#features" class="header__nav-link">Преимущества</a>
          <a href="#how" class="header__nav-link">Как это работает</a>
          <RouterLink to="/login" class="header__btn">Войти</RouterLink>
        </nav>
      </div>
    </header>

    <section class="hero">
      <div class="hero__content">
        <span class="hero__badge">SaaS Платформа №1 в СНГ</span>
        <h1 class="hero__title">
          Управляйте<br />заявками на<br />ремонт<br /><span class="hero__title-highlight">эффективно</span>
        </h1>
        <p class="hero__desc">
          Профессиональная SaaS-платформа для управления сервисным обслуживанием. Оптимизируйте процессы, отслеживайте мастеров и повышайте лояльность клиентов в одном интерфейсе.
        </p>
        <div class="hero__cta">
          <button type="button" class="hero__btn-primary">
            Начать бесплатно
            <img :src="assets.arrow" alt="" />
          </button>
          <button type="button" class="hero__btn-secondary">Связаться с нами</button>
        </div>
      </div>
      <div class="hero__visual">
        <div class="hero__dashboard">
          <img v-if="assets.dashboard" :src="assets.dashboard" alt="Предпросмотр панели управления" />
          <div v-else class="hero__dashboard-placeholder" />
        </div>
      </div>
    </section>

    <section id="features" class="features">
      <div class="features__inner">
        <div class="features__header">
          <h2 class="features__title">Преимущества нашей системы</h2>
          <p class="features__desc">
            Все необходимые инструменты для эффективного масштабирования вашей ремонтной службы в одном месте.
          </p>
        </div>
        <div class="features__grid">
          <article class="feature-card">
            <div class="feature-card__icon">
              <img :src="assets.iconAssign" alt="" />
            </div>
            <h3 class="feature-card__title">Быстрое назначение</h3>
            <p class="feature-card__text">
              Мгновенное распределение задач между мастерами на основе их квалификации, геолокации и текущей загруженности.
            </p>
          </article>
          <article class="feature-card">
            <div class="feature-card__icon">
              <img :src="assets.iconStatus" alt="" />
            </div>
            <h3 class="feature-card__title">Контроль статусов</h3>
            <p class="feature-card__text">
              Полный прозрачный контроль жизненного цикла заявки от момента создания до подписания акта выполненных работ.
            </p>
          </article>
          <article class="feature-card">
            <div class="feature-card__icon">
              <img :src="assets.iconReport" alt="" />
            </div>
            <h3 class="feature-card__title">Отчетность</h3>
            <p class="feature-card__text">
              Глубокая аналитика KPI мастеров, затрат на запчасти и времени выполнения работ для принятия верных управленческих решений.
            </p>
          </article>
        </div>
      </div>
    </section>

    <section class="cta-banner">
      <div class="cta-banner__inner">
        <h2 class="cta-banner__title">Готовы оптимизировать<br />ваш сервис?</h2>
        <p class="cta-banner__desc">
          Присоединяйтесь к сотням сервисных центров и управляющих компаний, которые уже автоматизировали свои процессы.
        </p>
        <div class="cta-banner__btns">
          <button type="button" class="cta-banner__btn-primary">Попробовать бесплатно</button>
          <button type="button" class="cta-banner__btn-secondary">Записаться на демо</button>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="footer__inner">
        <RouterLink to="/" class="footer__logo">
          <img :src="assets.logoFooter" alt="" class="footer__logo-icon" />
          <span class="footer__logo-text">Ремонтная<br />служба</span>
        </RouterLink>
        <div class="footer__links">
          <a href="#" class="footer__link">Политика конфиденциальности</a>
          <a href="#" class="footer__link">Условия использования</a>
          <a href="#" class="footer__link">Служба поддержки</a>
        </div>
        <p class="footer__copy">© {{ currentYear }} Ремонтная служба. Все права защищены.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const assets = {
  logo: '/logo.svg',
  logoFooter: '/logo.svg',
  dashboard: null,
  arrow: '/arrow.svg',
  iconAssign: '/icon-assign.svg',
  iconStatus: '/icon-status.svg',
  iconReport: '/icon-report.svg',
}

const currentYear = computed(() => new Date().getFullYear())
</script>
