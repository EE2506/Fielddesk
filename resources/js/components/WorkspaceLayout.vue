<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const brandImage = '/fielddesk.svg'

const props = defineProps<{ title: string; portal?: boolean }>()
const navigation = computed(() => props.portal
  ? [{ label: 'Find an agent', href: '/portal' }]
  : [
      { label: 'Today', href: '/workspace' },
      { label: 'Clients', href: '/workspace/clients' },
      { label: 'Introductions', href: '/workspace/introductions' },
      { label: 'Settings', href: '/workspace/settings' },
    ])
</script>

<template>
  <Head :title="title" />
  <a class="skip-link" href="#content">Skip to content</a>
  <div class="workspace-shell">
    <aside class="workspace-sidebar">
      <a class="brand-lockup" href="/"><img :src="brandImage" width="34" height="34" alt=""> Fielddesk</a>
      <nav class="workspace-nav" :aria-label="portal ? 'Client portal' : 'Agent workspace'">
        <Link v-for="item in navigation" :key="item.href" :href="item.href"
          :aria-current="($page.url.split('?')[0] === item.href || (item.href === '/workspace/clients' && $page.url.startsWith('/workspace/clients/'))) ? 'page' : undefined">
          {{ item.label }}
        </Link>
      </nav>
      <Link class="sign-out" href="/workspace/logout" method="post" as="button">Sign out</Link>
    </aside>
    <main id="content" class="workspace-content">
      <slot />
    </main>
  </div>
</template>
