<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

defineProps<{ page: { prev_page_url: string | null; next_page_url: string | null }; label: string }>()
</script>

<template>
  <nav v-if="page.prev_page_url || page.next_page_url" class="actions pager" :aria-label="label">
    <Link v-if="page.prev_page_url" :href="page.prev_page_url" preserve-scroll>Previous</Link>
    <Link v-if="page.next_page_url" :href="page.next_page_url" preserve-scroll>Next</Link>
  </nav>
</template>
