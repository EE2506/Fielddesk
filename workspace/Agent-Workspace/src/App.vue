<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import { Bell, CalendarDays, ChevronRight, CircleUserRound, Home, MessageSquare, Phone, Search, Settings, ShieldCheck, Users, X } from 'lucide-vue-next'
import { closeSimulation, simulation, state, toastMessage } from './stores/workspace'
import { formatLongDate } from './utils'

const route = useRoute()
const router = useRouter()
const title = computed(() => String(route.meta.title || 'Today'))
const todayLabel = computed(() => formatLongDate(state.demoDate))
const navItems = [
  { label: 'Today', to: '/', icon: Home },
  { label: 'Clients', to: '/clients', icon: Users },
  { label: 'Reminders', to: '/settings', icon: Bell },
]
function searchClients() { router.push('/clients') }
</script>

<template>
  <div class="app-shell">
    <aside class="sidebar">
      <RouterLink to="/" class="brand-lockup" aria-label="Insurance Agent Workspace home">
        <span class="brand-mark"><ShieldCheck :size="21" :stroke-width="2.3" /></span>
        <span class="brand-copy"><strong>Fielddesk</strong><small>AGENT WORKSPACE</small></span>
      </RouterLink>
      <div class="side-label">WORKSPACE</div>
      <nav class="side-nav" aria-label="Main navigation">
        <RouterLink v-for="item in navItems" :key="item.to" :to="item.to" class="side-link" exact-active-class="selected">
          <component :is="item.icon" :size="19" :stroke-width="2" />
          <span>{{ item.label }}</span>
          <span v-if="item.label === 'Today'" class="nav-dot" aria-hidden="true"></span>
        </RouterLink>
      </nav>
      <div class="sidebar-note">
        <div class="side-label">YOUR DAY</div>
        <div class="sidebar-date"><CalendarDays :size="16" /><span>Manila, Philippines</span></div>
        <strong>{{ todayLabel }}</strong>
        <span class="privacy-note">Private workspace · demo data</span>
      </div>
      <div class="sidebar-profile">
        <div class="avatar avatar-agent">AR</div>
        <div><strong>Ana Reyes</strong><span>Insurance agent</span></div>
        <button class="icon-button quiet-icon" aria-label="Open reminder settings" @click="router.push('/settings')"><Settings :size="17" /></button>
      </div>
    </aside>

    <div class="main-column">
      <header class="topbar">
        <div class="topbar-mobile-brand">
          <span class="brand-mark"><ShieldCheck :size="19" :stroke-width="2.3" /></span>
          <strong>Fielddesk</strong>
        </div>
        <div class="topbar-title"><span class="crumb">WORKSPACE</span><ChevronRight :size="14" /><strong>{{ title }}</strong></div>
        <div class="topbar-actions">
          <button class="search-launch" @click="searchClients"><Search :size="17" /><span>Find a client</span><kbd>⌘ K</kbd></button>
          <div class="topbar-date"><span class="status-dot"></span><span>Manila · {{ todayLabel }}</span></div>
          <button class="icon-button notification-button" aria-label="Reminder settings" @click="router.push('/settings')"><Bell :size="18" /><i></i></button>
          <button class="profile-button" aria-label="Open agent settings" @click="router.push('/settings')"><span class="avatar avatar-agent">AR</span></button>
        </div>
      </header>

      <main class="page-content"><RouterView /></main>
    </div>

    <nav class="mobile-nav" aria-label="Mobile navigation">
      <RouterLink to="/" exact-active-class="active"><Home :size="20" /><span>Today</span></RouterLink>
      <RouterLink to="/clients" active-class="active"><Users :size="20" /><span>Clients</span></RouterLink>
      <RouterLink to="/settings" active-class="active"><Bell :size="20" /><span>Reminders</span></RouterLink>
      <button @click="router.push('/clients/new')"><CircleUserRound :size="20" /><span>Add client</span></button>
    </nav>

    <Transition name="fade">
      <div v-if="simulation" class="modal-backdrop" @click.self="closeSimulation">
        <section class="simulation-dialog" role="dialog" aria-modal="true" :aria-label="simulation.action === 'call' ? 'Simulated call' : 'Simulated text'">
          <button class="icon-button dialog-close" aria-label="Close" @click="closeSimulation"><X :size="19" /></button>
          <div class="simulation-icon"><component :is="simulation.action === 'call' ? Phone : MessageSquare" :size="23" /></div>
          <span class="eyebrow">DEMO ACTION · NO MESSAGE SENT</span>
          <h2>{{ simulation.action === 'call' ? `Calling ${simulation.clientName}` : `Text ${simulation.clientName}` }}</h2>
          <p>{{ simulation.action === 'call' ? 'This prototype does not place real calls.' : 'This prototype does not send real texts.' }} The contact number stays masked.</p>
          <div class="masked-number">{{ simulation.mobile.startsWith('+63') ? `+63 ${simulation.mobile.slice(3, 6)} ••• ${simulation.mobile.slice(-4)}` : '••• ••• ••••' }}</div>
          <button class="button button-primary full-width" @click="closeSimulation">Done</button>
        </section>
      </div>
    </Transition>

    <Transition name="toast"><div v-if="toastMessage" class="toast-message" role="status">{{ toastMessage }}</div></Transition>
  </div>
</template>
