import { createRouter, createWebHistory } from 'vue-router'
import Landing from '../views/Landing.vue'
import Login from '../views/Login.vue'
import AppLayout from '../layouts/AppLayout.vue'
import DispatcherLayout from '../layouts/DispatcherLayout.vue'
import MasterLayout from '../layouts/MasterLayout.vue'
import CreateRequest from '../views/CreateRequest.vue'
import RequestsList from '../views/RequestsList.vue'
import MasterPanel from '../views/MasterPanel.vue'
import Dashboard from '../views/Dashboard.vue'
import Clients from '../views/Clients.vue'
import Settings from '../views/Settings.vue'

const routes = [
  { path: '/', name: 'landing', component: Landing },
  { path: '/login', name: 'login', component: Login },
  {
    path: '/app',
    component: AppLayout,
    children: [
      { path: '', redirect: { name: 'create-request' } },
      {
        path: 'requests/create',
        name: 'create-request',
        component: CreateRequest,
        meta: { title: 'Создать заявку' },
      },
      {
        path: 'clients',
        name: 'clients',
        component: Clients,
        meta: { title: 'Клиенты' },
      },
      {
        path: 'settings',
        name: 'settings',
        component: Settings,
        meta: { title: 'Настройки' },
      },
    ],
  },
  {
    path: '/dispatcher',
    component: DispatcherLayout,
    children: [
      { path: '', redirect: { name: 'requests-list' } },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Дашборд' },
      },
      {
        path: 'requests',
        name: 'requests-list',
        component: RequestsList,
        meta: { title: 'Заявки на сервис' },
      },
      {
        path: 'masters',
        name: 'masters',
        component: () => import('../views/Masters.vue'),
        meta: { title: 'Мастера' },
      },
      {
        path: 'clients',
        name: 'dispatcher-clients',
        component: Clients,
        meta: { title: 'Клиенты' },
      },
      {
        path: 'reports',
        name: 'reports',
        component: () => import('../views/Reports.vue'),
        meta: { title: 'Отчеты' },
      },
      {
        path: 'settings',
        name: 'dispatcher-settings',
        component: Settings,
        meta: { title: 'Настройки' },
      },
    ],
  },
  {
    path: '/master',
    component: MasterLayout,
    children: [
      { path: '', redirect: { name: 'master-tasks' } },
      {
        path: 'tasks',
        name: 'master-tasks',
        component: MasterPanel,
        meta: { title: 'Мои заявки' },
      },
      {
        path: 'history',
        name: 'master-history',
        component: () => import('../views/MasterHistory.vue'),
        meta: { title: 'История' },
      },
      {
        path: 'settings',
        name: 'master-settings',
        component: () => import('../views/MasterSettings.vue'),
        meta: { title: 'Настройки' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
