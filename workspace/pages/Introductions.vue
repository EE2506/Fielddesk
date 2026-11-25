<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import Pager from '../../resources/js/components/Pager.vue'
import WorkspaceLayout from '../../resources/js/components/WorkspaceLayout.vue'
import type { Page } from '../../resources/js/workspace'

defineProps<{ introductions: Page<{ id: number; client_name: string; client_email: string | null; status: string }> }>()
const form = useForm({ status: '' })

function respond(id: number, status: 'Accepted' | 'Declined') {
  form.status = status
  form.patch('/workspace/introductions/' + id, { preserveScroll: true })
}
</script>

<template>
  <WorkspaceLayout title="Introductions">
    <header class="page-title"><h1>Introductions</h1><p>Review requests from people looking for an insurance agent.</p></header>
    <section class="panel">
      <h2>Requests</h2>
      <p v-if="!introductions.data.length" class="muted">No introduction requests yet. <Link href="/workspace/settings">Manage availability</Link></p>
      <ul v-else class="rows">
        <li v-for="item in introductions.data" :key="item.id" class="intro-row">
          <div><strong>{{ item.client_name }}</strong><p class="muted">{{ item.status }}</p>
            <a v-if="item.client_email" :href="'mailto:' + item.client_email">{{ item.client_email }}</a>
          </div>
          <div v-if="item.status === 'Pending'" class="actions">
            <button type="button" class="outline-button" :disabled="form.processing" @click="respond(item.id, 'Accepted')">Accept</button>
            <button type="button" class="outline-button" :disabled="form.processing" @click="respond(item.id, 'Declined')">Decline</button>
          </div>
        </li>
      </ul>
      <Pager :page="introductions" label="Introduction request pages" />
    </section>
  </WorkspaceLayout>
</template>
