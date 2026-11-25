import { createRouter, createWebHistory } from 'vue-router'
import TodayView from './views/TodayView.vue'
import ClientsView from './views/ClientsView.vue'
import ClientDetailView from './views/ClientDetailView.vue'
import PolicyDetailView from './views/PolicyDetailView.vue'
import ContactLogView from './views/ContactLogView.vue'
import ClientFormView from './views/ClientFormView.vue'
import PolicyFormView from './views/PolicyFormView.vue'
import SettingsView from './views/SettingsView.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: TodayView, meta: { title: 'Today' } },
    { path: '/clients', component: ClientsView, meta: { title: 'Clients' } },
    { path: '/clients/new', component: ClientFormView, meta: { title: 'Add client' } },
    { path: '/clients/:clientId/edit', component: ClientFormView, meta: { title: 'Edit client' } },
    { path: '/clients/:clientId', component: ClientDetailView, meta: { title: 'Client details' } },
    { path: '/policies/new/:clientId', component: PolicyFormView, meta: { title: 'Add policy' } },
    { path: '/policies/:policyId/edit', component: PolicyFormView, meta: { title: 'Edit policy' } },
    { path: '/policies/:policyId', component: PolicyDetailView, meta: { title: 'Policy details' } },
    { path: '/log/:clientId', component: ContactLogView, meta: { title: 'Log contact' } },
    { path: '/contact/:clientId/:contactId/edit', component: ContactLogView, meta: { title: 'Edit conversation' } },
    { path: '/settings', component: SettingsView, meta: { title: 'Reminders' } },
  ],
  scrollBehavior: () => ({ top: 0, behavior: 'smooth' }),
})
