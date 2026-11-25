<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import Pager from '../components/Pager.vue'
import WorkspaceLayout from '../components/WorkspaceLayout.vue'
import type { Page } from '../workspace'

defineProps<{
  agents: Page<{ id: number; name: string }>
  introductions: Page<{ id: number; agent_name: string; status: string }>
}>()
const form = useForm({})
</script>

<template>
  <WorkspaceLayout title="Find an agent" portal>
    <header class="page-title"><h1>Find an agent</h1><p>Request an introduction. If the agent accepts, they can see your email and get in touch.</p></header>
    <div class="day-columns">
      <section class="panel">
        <h2>Available agents</h2>
        <p v-if="!agents.data.length" class="muted">No agents are available to request right now.</p>
        <ul v-else class="rows">
          <li v-for="agent in agents.data" :key="agent.id" class="intro-row">
            <strong>{{ agent.name }}</strong>
            <button type="button" class="outline-button" :disabled="form.processing" @click="form.post('/portal/agents/' + agent.id)">Request introduction<span class="sr-only"> to {{ agent.name }}</span></button>
          </li>
        </ul>
        <Pager :page="agents" label="Available agent pages" />
      </section>
      <section class="panel">
        <h2>Your requests</h2>
        <p v-if="!introductions.data.length" class="muted">You haven’t requested an introduction yet.</p>
        <ul v-else class="rows">
          <li v-for="item in introductions.data" :key="item.id" class="intro-row"><strong>{{ item.agent_name }}</strong><span class="muted">{{ item.status }}</span></li>
        </ul>
        <Pager :page="introductions" label="Introduction request pages" />
      </section>
    </div>
  </WorkspaceLayout>
</template>
