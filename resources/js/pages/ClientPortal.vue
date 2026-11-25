<script setup lang="ts">
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import ClientPortalLayout from '../components/ClientPortalLayout.vue'
import Pager from '../components/Pager.vue'
import type { Page } from '../workspace'

const props = defineProps<{
  profile: { name: string; email: string }
  connectedAgent: { agent_name: string } | null
  agents: Page<{ id: number; name: string }>
  introductions: Page<{ id: number; agent_name: string; status: string }>
}>()

const form = useForm({})
const firstName = computed(() => props.profile.name.trim().split(/\s+/)[0] || 'there')
const agentInitials = computed(() => props.connectedAgent?.agent_name.trim()
  .split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase())
</script>

<template>
  <ClientPortalLayout title="Client dashboard" main-class="portal-dashboard-main" home="/portal">
    <template #header-actions>
      <Link href="/workspace/logout" method="post" as="button" class="portal-switch-link">Sign out</Link>
    </template>

    <section class="portal-dashboard-heading">
      <div>
        <p class="portal-eyebrow"><span aria-hidden="true"></span> Your client dashboard</p>
        <h1>Hello, {{ firstName }}.</h1>
        <p class="portal-heading-copy">See your agent connection, introduction requests, and policy information.</p>
      </div>
      <span v-if="connectedAgent" class="portal-connected-pill">Connected to {{ connectedAgent.agent_name }}</span>
    </section>

    <section class="portal-next-due" aria-labelledby="client-next-date">
      <div class="portal-due-mark" aria-hidden="true">!</div>
      <div class="portal-due-copy">
        <h2 id="client-next-date">Upcoming policy dates</h2>
        <p>Policy dates aren’t available in the Client Portal yet.</p>
      </div>
    </section>

    <div class="portal-dashboard-grid">
      <div class="portal-dashboard-stack">
        <section class="portal-dashboard-card" aria-labelledby="client-policies">
          <h2 id="client-policies">Your policies</h2>
          <p>Policy records aren’t linked to Client accounts yet. Confirm policy details with your agent.</p>
        </section>

        <section id="available-agents" class="portal-dashboard-card" aria-labelledby="available-agents-title">
          <h2 id="available-agents-title">Available agents</h2>
          <p>When an agent accepts your request, they can see your email address and get in touch.</p>
          <p v-if="!agents.data.length">No agents are available to request right now.</p>
          <ul v-else class="portal-dashboard-rows">
            <li v-for="agent in agents.data" :key="agent.id">
              <strong>{{ agent.name }}</strong>
              <button type="button" class="portal-button portal-button-primary" :disabled="form.processing"
                @click="form.post('/portal/agents/' + agent.id, { preserveScroll: true })">
                Request introduction<span class="sr-only"> to {{ agent.name }}</span>
              </button>
            </li>
          </ul>
          <Pager :page="agents" label="Available agent pages" />
        </section>

        <section class="portal-dashboard-card" aria-labelledby="client-requests">
          <h2 id="client-requests">Your requests</h2>
          <p v-if="!introductions.data.length">You haven’t requested an introduction yet.</p>
          <ul v-else class="portal-dashboard-rows">
            <li v-for="item in introductions.data" :key="item.id">
              <strong>{{ item.agent_name }}</strong>
              <span class="portal-request-status" :class="{ 'portal-connected-pill': item.status === 'Accepted' }">{{ item.status }}</span>
            </li>
          </ul>
          <Pager :page="introductions" label="Introduction request pages" />
        </section>
      </div>

      <aside class="portal-dashboard-stack" aria-label="Agent and profile">
        <section class="portal-dashboard-card" aria-labelledby="client-agent">
          <h2 id="client-agent">Your agent</h2>
          <div v-if="connectedAgent" class="portal-agent-profile">
            <span class="portal-agent-avatar" aria-hidden="true">{{ agentInitials }}</span>
            <div><strong>{{ connectedAgent.agent_name }}</strong><p>Accepted your introduction</p></div>
          </div>
          <p v-else>When an agent accepts your introduction request, their name will appear here.</p>
          <p>Messages and call requests aren’t available yet.</p>
        </section>

        <section class="portal-dashboard-card" aria-labelledby="client-profile">
          <h2 id="client-profile">Your profile</h2>
          <dl class="portal-profile-list">
            <div><dt>Name</dt><dd>{{ profile.name }}</dd></div>
            <div><dt>Email</dt><dd>{{ profile.email }}</dd></div>
          </dl>
        </section>
      </aside>
    </div>
  </ClientPortalLayout>
</template>
