<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ArrowLeft, ArrowUpRight, CarFront, ChevronRight, Clock3, FileText, Heart, MessageSquare, Phone, Plus, Shield, SquarePen, UserRound } from 'lucide-vue-next'
import { getClient, getNextCallback, getPoliciesForClient, getContactsForClient, getPolicy, showSimulatedAction, state } from '../stores/workspace'
import { formatDate, formatMoney, friendlyCallback, maskMobile, daysBetween } from '../utils'
import EmptyState from './EmptyState.vue'

const route = useRoute()
const router = useRouter()
const clientId = computed(() => String(route.params.clientId || ''))
const client = computed(() => getClient(clientId.value))
const policies = computed(() => getPoliciesForClient(clientId.value))
const nextCallback = computed(() => getNextCallback(clientId.value))
const conversations = computed(() => getContactsForClient(clientId.value))
function policyDue(policyId: string) { const p = getPolicy(policyId); return p?.premiumDue || p?.renewalDue }
function policyDueLabel(policyId: string) { const due = policyDue(policyId); if (!due) return 'No upcoming deadline'; const p = getPolicy(policyId); return `${p?.kind === 'Motor' ? 'Renewal due' : 'Premium due'} ${formatDate(due)}` }
function duePill(policyId: string) { const due = policyDue(policyId); if (!due) return 'pill-neutral'; const days = daysBetween(state.demoDate, due); return days <= 3 ? 'pill-critical' : 'pill-warning' }
function initials(name: string) { return name.split(' ').filter(Boolean).slice(0, 2).map(part => part[0]).join('').toUpperCase() }
</script>

<template>
  <button class="back-link" @click="router.push('/clients')"><ArrowLeft :size="15" /> Back to clients</button>
  <div v-if="client">
    <div class="detail-head">
      <div class="detail-identity"><div class="detail-avatar">{{ initials(client.name) }}</div><div><h1>{{ client.name }}</h1><p>{{ client.nickname && client.nickname !== client.name ? `“${client.nickname}” · ` : '' }}{{ client.reference }} · {{ maskMobile(client.mobile) }}</p></div></div>
      <div class="detail-head-actions"><RouterLink :to="`/clients/${client.id}/edit`" class="button button-secondary button-small"><SquarePen :size="14" /> Edit client</RouterLink></div>
    </div>

    <div class="detail-layout">
      <div class="detail-main">
        <section class="card card-pad">
          <div class="section-heading"><div><h2>Policies</h2><p>Each policy keeps its own dates and documents.</p></div><RouterLink :to="`/policies/new/${client.id}`" class="button button-secondary button-small"><Plus :size="14" /> Add policy</RouterLink></div>
          <div v-if="policies.length" class="policy-mini-list">
            <RouterLink v-for="policy in policies" :key="policy.id" :to="`/policies/${policy.id}`" class="policy-mini">
              <span class="policy-icon"><CarFront v-if="policy.kind === 'Motor'" :size="18" /><Heart v-else :size="18" /></span>
              <span class="policy-mini-copy"><strong>{{ policy.kind }} · {{ policy.product }}</strong><span>{{ policy.insurer }} · {{ policy.vehicle || policy.policyNumber }}</span></span>
              <span class="policy-mini-right"><strong>{{ policyDueLabel(policy.id) }}</strong><span :class="['pill', duePill(policy.id)]">{{ policyDue(policy.id) ? formatDate(policyDue(policy.id)!) : 'Active' }}</span><ChevronRight :size="15" color="#8296a5" /></span>
            </RouterLink>
          </div>
          <EmptyState v-else title="No policies added" description="Add a policy to keep deadlines and documents linked to this client." action-label="Add policy" @action="router.push(`/policies/new/${client.id}`)" />
        </section>

        <section class="card card-pad">
          <div class="section-heading"><div><h2>Recent conversations</h2><p>What you agreed on last time.</p></div><RouterLink :to="`/log/${client.id}`" class="text-link">Log contact <ChevronRight :size="13" style="vertical-align:-3px" /></RouterLink></div>
          <div v-if="conversations.length" class="conversation-list">
            <article v-for="contact in conversations" :key="contact.id" class="conversation-item">
              <div class="conversation-head"><strong>{{ contact.outcome }}</strong><time>{{ formatDate(contact.happenedOn, { day:'numeric', month:'short', year:'numeric' }) }} · {{ contact.happenedAt }}</time></div>
              <div class="conversation-meta"><span>{{ getPolicy(contact.policyId)?.kind }} · {{ getPolicy(contact.policyId)?.product }}</span><span class="pill pill-neutral">{{ getPolicy(contact.policyId)?.insurer }}</span><span v-if="contact.callbackId" class="pill pill-info">Callback set</span></div>
              <p class="conversation-note">{{ contact.note || 'No note was added.' }}</p>
              <div class="conversation-actions"><RouterLink :to="`/contact/${client.id}/${contact.id}/edit`" class="text-link">Edit note</RouterLink></div>
            </article>
          </div>
          <EmptyState v-else title="No conversations logged" description="Save your first call or message here. Notes stay in this local demo." action-label="Log contact" @action="router.push(`/log/${client.id}`)" />
        </section>
      </div>

      <aside class="detail-aside">
        <div class="contact-action-bar">
          <button class="action-shortcut" @click="showSimulatedAction('call', client)"><Phone :size="15" /> Call</button>
          <button class="action-shortcut" @click="showSimulatedAction('text', client)"><MessageSquare :size="15" /> Text</button>
          <RouterLink class="action-shortcut primary" :to="`/log/${client.id}`"><FileText :size="15" /> Log contact</RouterLink>
        </div>
        <section class="card next-callback-card">
          <div class="next-callback-top"><span class="eyebrow">NEXT PROMISED CALLBACK</span><span class="pill pill-info"><Clock3 :size="11" /> Next action</span></div>
          <template v-if="nextCallback">
            <div class="callback-date-large" style="margin-top:13px">{{ friendlyCallback(nextCallback.date, nextCallback.time, state.demoDate) }}</div>
            <h2>{{ nextCallback.reason }}</h2><p>{{ getPolicy(nextCallback.policyId)?.kind }} · {{ getPolicy(nextCallback.policyId)?.product }}<br>Last agreed: {{ conversations[0]?.note || 'Follow up as promised.' }}</p>
          </template>
          <template v-else>
            <h2>No callback promised yet</h2><p>After the call, add what you agreed and set a date and time here.</p>
            <RouterLink :to="`/log/${client.id}`" class="text-link" style="padding-left:0;margin-top:8px">Set a callback <ChevronRight :size="13" style="vertical-align:-3px" /></RouterLink>
          </template>
        </section>
        <section class="card card-pad">
          <div class="section-heading" style="margin-bottom:10px"><h2>Client details</h2><UserRound :size="16" color="#71889a" /></div>
          <div style="display:grid;gap:11px">
            <div><span class="eyebrow">MOBILE</span><div style="font-size:11px;font-weight:700;color:#304b60;margin-top:5px">{{ maskMobile(client.mobile) }}</div></div>
            <div><span class="eyebrow">CLIENT REFERENCE</span><div style="font-size:11px;font-weight:700;color:#304b60;margin-top:5px">{{ client.reference }}</div></div>
            <div v-if="client.notes"><span class="eyebrow">AGENT NOTE</span><div style="font-size:10px;line-height:1.5;color:#667d90;margin-top:5px">{{ client.notes }}</div></div>
          </div>
        </section>
      </aside>
    </div>
  </div>
  <section v-else class="card card-pad"><EmptyState title="Client not found" description="This fictional client may have been removed by a demo reset." action-label="Back to clients" @action="router.push('/clients')" /></section>
</template>
