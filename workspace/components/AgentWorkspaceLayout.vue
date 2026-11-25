<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import '../styles/agent-workspace.css'

defineProps<{ title: string; date?: string }>()

const nav = [
  { label: 'Today', href: '/workspace', icon: 'M3 10 12 3l9 7v10H3z M9 20v-6h6v6' },
  { label: 'Clients', href: '/workspace/clients', icon: 'M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M2 21v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2 M16 4a4 4 0 0 1 0 8 M22 21v-2a5 5 0 0 0-4-5' },
  { label: 'Introductions', href: '/workspace/introductions', icon: 'M4 4h16v13H8l-4 3z M8 9h8 M8 13h5' },
  { label: 'Reminders', href: '/workspace/settings', icon: 'M18 8a6 6 0 0 0-12 0v4l-2 4h16l-2-4z M10 20h4' },
]

function current(url: string, href: string) {
  const path = url.split(/[?#]/, 1)[0]
  return path === href || (href === '/workspace/clients' && path.startsWith(href + '/'))
}
</script>

<template>
  <Head :title="title" />
  <div class="agent-workspace">
    <a class="skip-link" href="#agent-content">Skip to content</a>
    <aside class="agent-sidebar">
      <Link href="/workspace" class="agent-brand">
        <img :src="'/fielddesk.svg'" width="37" height="37" alt="">
        <span><strong>Fielddesk</strong><small>Agent workspace</small></span>
      </Link>
      <p class="agent-side-label">Workspace</p>
      <nav class="agent-side-nav" aria-label="Workspace">
        <Link v-for="item in nav" :key="item.href" :href="item.href" :aria-current="current($page.url, item.href) ? 'page' : undefined">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="item.icon" /></svg>
          {{ item.label }}
        </Link>
      </nav>
      <div v-if="date" class="agent-sidebar-note"><span>Manila, Philippines</span><strong>{{ date }}</strong></div>
      <Link class="agent-signout" href="/workspace/logout" method="post" as="button">Sign out</Link>
    </aside>

    <div class="agent-main-column">
      <header class="agent-topbar">
        <Link href="/workspace" class="agent-brand agent-mobile-brand">
          <img :src="'/fielddesk.svg'" width="32" height="32" alt=""><strong>Fielddesk</strong>
        </Link>
        <div class="agent-breadcrumb"><span>Workspace</span><span aria-hidden="true">›</span><strong>{{ title }}</strong></div>
        <div class="agent-topbar-actions">
          <Link href="/workspace/clients" class="agent-search-link">Find a client</Link>
          <span v-if="date" class="agent-top-date">Manila · {{ date }}</span>
          <Link class="agent-mobile-signout" href="/workspace/logout" method="post" as="button">Sign out</Link>
        </div>
      </header>
      <main id="agent-content" class="agent-page-content"><slot /></main>
    </div>

    <nav class="agent-mobile-nav" aria-label="Workspace">
      <Link v-for="item in nav" :key="item.href" :href="item.href" :aria-current="current($page.url, item.href) ? 'page' : undefined">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="item.icon" /></svg>
        {{ item.label }}
      </Link>
    </nav>
  </div>
</template>
