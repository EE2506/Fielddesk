<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import ClientForm from '../../resources/js/components/ClientForm.vue'
import ContactForm from '../../resources/js/components/ContactForm.vue'
import DocumentChecklist from '../../resources/js/components/DocumentChecklist.vue'
import Pager from '../../resources/js/components/Pager.vue'
import PolicyForm from '../../resources/js/components/PolicyForm.vue'
import AgentWorkspaceLayout from '../components/AgentWorkspaceLayout.vue'
import { calendarDate, clientName, clientUrl, manilaTime, type Callback, type Client, type Page, type Policy } from '../../resources/js/workspace'

const props = defineProps<{
  client: Client
  policies: Policy[]
  followUps: Callback[]
  selectedCallback: number | null
  contacts: Page<{ id: number; created_at: string; outcome: string; note: string | null; policy: { product: string } | null }>
}>()
const source = computed(() => props.followUps.find((item) => item.id === props.selectedCallback))
</script>

<template>
  <AgentWorkspaceLayout :title="clientName(client)">
    <datalist id="event-types">
      <option value="Renewal"></option><option value="End of cover"></option>
      <option value="Premium due"></option><option value="Review"></option>
    </datalist>
    <header class="page-title">
      <Link href="/workspace/clients">All clients</Link>
      <div class="agent-client-identity">
        <span class="agent-initial" aria-hidden="true">{{ clientName(client).slice(0, 1).toUpperCase() }}</span>
        <div><h1>{{ clientName(client) }}</h1><p class="muted">{{ client.mobile }} · {{ client.reference }}</p></div>
      </div>
      <div class="actions agent-contact-actions">
        <a :href="'tel:' + client.mobile">Call</a>
        <a :href="'sms:' + client.mobile">Text</a>
        <a href="#log-contact">Log contact</a>
      </div>
    </header>
    <div class="day-columns agent-detail-layout">
      <div class="stack">
        <section class="panel" id="log-contact">
          <h2>{{ source ? 'Log callback outcome' : 'Log contact' }}</h2>
          <p v-if="source">{{ source.reason }} · {{ manilaTime(source.due_at) }}</p>
          <ContactForm :key="source?.id ?? 'general'" :client-id="client.id" :policies="policies" :source="source" />
        </section>
        <section class="panel">
          <h2>Policies</h2>
          <p v-if="!policies.length" class="muted">No policies added.</p>
          <article v-for="policy in policies" :id="'policy-' + policy.id" :key="policy.id" class="policy">
            <div class="agent-policy-summary">
              <h3>{{ policy.product }}</h3>
              <p>{{ policy.insurer }} · {{ policy.event_label }} · {{ calendarDate(policy.due_on) }}</p>
              <p v-if="policy.reference" class="muted">Policy reference · {{ policy.reference }}</p>
            </div>
            <details v-if="policy.filing_reference"><summary>Private filing reference</summary><p>{{ policy.filing_reference }}</p></details>
            <DocumentChecklist :client-id="client.id" :policy-id="policy.id" :documents="policy.documents" />
            <details><summary>Edit policy</summary><PolicyForm :client-id="client.id" :policy="policy" /></details>
          </article>
          <details><summary>Add policy</summary><PolicyForm :client-id="client.id" /></details>
        </section>
      </div>
      <div class="stack">
        <section class="panel">
          <h2>Pending callbacks</h2>
          <p v-if="!followUps.length" class="muted">No pending callbacks.</p>
          <ul class="rows">
            <li v-for="item in followUps" :key="item.id">
              <strong>{{ item.reason }}</strong>
              <p>{{ manilaTime(item.due_at) }}<template v-if="item.policy"> · {{ item.policy.product }}</template></p>
              <Link :href="clientUrl(client.id) + '?callback=' + item.id + '#log-contact'">Log callback outcome</Link>
            </li>
          </ul>
        </section>
        <section class="panel">
          <h2>Contact history</h2>
          <p v-if="!contacts.data.length" class="muted">No contacts logged.</p>
          <ul class="rows">
            <li v-for="contact in contacts.data" :key="contact.id">
              <strong>{{ contact.outcome }}</strong>
              <p class="muted">{{ manilaTime(contact.created_at) }}<template v-if="contact.policy"> · {{ contact.policy.product }}</template></p>
              <p v-if="contact.note">{{ contact.note }}</p>
            </li>
          </ul>
          <Pager :page="contacts" label="Contact history pages" />
        </section>
        <section class="panel">
          <details><summary>Edit client</summary><ClientForm :key="client.mobile" :client="client" /></details>
        </section>
      </div>
    </div>
  </AgentWorkspaceLayout>
</template>
