<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import '../../css/client-portal.css'

defineOptions({ inheritAttrs: false })
defineProps<{ title: string; mainClass?: string; home?: string }>()
</script>

<template>
  <Head :title="title" />
  <div class="portal-page">
    <a class="skip-link" href="#client-portal-content">Skip to content</a>
    <header class="portal-topbar portal-frame">
      <a :href="home ?? '/'" class="portal-brand" aria-label="Fielddesk home">
        <span class="portal-brand-mark" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round">
            <path d="M12 2.8 20 6v5.6c0 5.3-3.3 8.3-8 10.6-4.7-2.3-8-5.3-8-10.6V6l8-3.2Z" />
            <path d="m8.3 12.1 2.3 2.3 5.1-5.2" stroke-linecap="round" />
          </svg>
        </span>
        <span><strong>Fielddesk</strong><small>Client portal</small></span>
      </a>
      <slot name="header-actions">
        <Link href="/login" class="portal-switch-link">Sign in <span aria-hidden="true">↗</span></Link>
      </slot>
    </header>
    <main id="client-portal-content" class="portal-frame" :class="mainClass">
      <slot />
    </main>
    <footer class="portal-footer portal-frame"><span>Fielddesk · Client portal</span></footer>
  </div>
</template>
