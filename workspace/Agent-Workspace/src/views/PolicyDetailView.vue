<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ArrowLeft, ArrowUpRight, CalendarClock, Check, CheckCircle2, CircleAlert, FileClock, FileText, LockKeyhole, ShieldCheck, SquarePen } from 'lucide-vue-next'
import { getClient, getPolicy, markDocumentReceived, getNextCallback, state } from '../stores/workspace'
import { daysBetween, formatDate, formatMoney, friendlyCallback } from '../utils'
import EmptyState from './EmptyState.vue'

const route = useRoute()
const router = useRouter()
const policy = computed(() => getPolicy(String(route.params.policyId || '')))
const client = computed(() => policy.value ? getClient(policy.value.clientId) : undefined)
const nextCallback = computed(() => client.value ? getNextCallback(client.value.id) : undefined)
const revealReference = ref(true)
const dueDate = computed(() => policy.value?.renewalDue || policy.value?.premiumDue)
const dueLabel = computed(() => policy.value?.kind === 'Motor' ? 'Renewal due' : 'Premium due')
const daysToDue = computed(() => dueDate.value ? daysBetween(state.demoDate, dueDate.value) : 999)
function statusIcon(status: string) { return status === 'Missing' ? CircleAlert : status === 'Received' ? CheckCircle2 : FileClock }
function statusClass(status: string) { return status.toLowerCase() }
function receive(documentId: string) { if (policy.value) markDocumentReceived(policy.value.id, documentId) }
</script>

<template>
  <button class="back-link" @click="router.push(client ? `/clients/${client.id}` : '/clients')"><ArrowLeft :size="15" /> Back to {{ client?.nickname || 'client' }}</button>
  <div v-if="policy && client">
    <section class="policy-hero">
      <div class="policy-hero-top"><div><span class="eyebrow">{{ policy.insurer.toUpperCase() }} · {{ policy.kind.toUpperCase() }} POLICY</span><h1>{{ policy.product }}</h1><p>{{ client.name }} · {{ policy.vehicle || 'Individual life cover' }}</p></div><span class="pill">Active policy</span></div>
      <div class="policy-number">Policy {{ policy.policyNumber }} <span aria-hidden="true">·</span> {{ formatMoney(policy.premiumAmount) }} premium</div>
      <div class="policy-stat-grid">
        <div class="policy-stat"><span>Coverage begins</span><strong>{{ formatDate(policy.coverageStart, { day:'numeric', month:'short', year:'numeric' }) }}</strong></div>
        <div class="policy-stat"><span>Coverage ends</span><strong>{{ formatDate(policy.coverageEnd, { day:'numeric', month:'short', year:'numeric' }) }}</strong></div>
        <div class="policy-stat"><span>{{ dueLabel }}</span><strong class="deadline-urgent">{{ dueDate ? formatDate(dueDate, { day:'numeric', month:'short' }) : 'Not set' }}</strong></div>
      </div>
    </section>

    <div class="page-head" style="align-items:center;margin-bottom:15px">
      <div><span class="eyebrow">POLICY RECORD · PRIVATE TO THE AGENT</span><h1 style="font-size:20px;margin:5px 0 0">Dates & documents</h1></div>
      <RouterLink :to="`/policies/${policy.id}/edit`" class="button button-secondary button-small"><SquarePen :size="14" /> Edit policy</RouterLink>
    </div>

    <div v-if="dueDate" class="deadline-callout">
      <span class="deadline-callout-icon"><CalendarClock :size="17" /></span>
      <div><strong>{{ dueLabel }} {{ formatDate(dueDate, { day:'numeric', month:'long', year:'numeric' }) }}</strong><span>{{ daysToDue === 0 ? 'Due today' : daysToDue === 1 ? 'Due tomorrow' : `Due in ${daysToDue} days` }} · Deadline belongs to this policy and is unchanged by contact logging or document updates.</span></div>
      <span :class="['pill', daysToDue <= 3 ? 'pill-critical' : 'pill-warning']">{{ daysToDue <= 3 ? 'Due soon' : 'Upcoming' }}</span>
    </div>

    <div class="detail-layout">
      <div class="detail-main">
        <section class="card card-pad">
          <div class="section-heading"><div><h2>Documents</h2><p>Track what is missing, received, or submitted.</p></div><span class="pill pill-neutral">{{ policy.documents.length }} items</span></div>
          <div v-if="policy.documents.length" class="documents-list">
            <div v-for="document in policy.documents" :key="document.id" class="document-row">
              <span :class="['document-status-icon', statusClass(document.status)]"><component :is="statusIcon(document.status)" :size="15" /></span>
              <span class="document-copy"><strong>{{ document.name }}</strong><span>{{ document.status === 'Missing' ? 'Not yet on file' : document.status === 'Received' ? 'Received from client' : 'Submitted to insurer' }}</span></span>
              <span :class="['pill', document.status === 'Missing' ? 'pill-warning' : document.status === 'Received' ? 'pill-success' : 'pill-info']">{{ document.status }}</span>
              <button v-if="document.status === 'Missing'" class="document-row-action" @click="receive(document.id)">Mark received</button>
              <span v-else class="document-row-action" style="color:#98a6b1"><Check :size="13" /></span>
            </div>
          </div>
          <EmptyState v-else title="No documents tracked" description="Add a document in Edit policy to keep its status here." />
        </section>

        <section class="card card-pad">
          <div class="section-heading"><div><h2>Policy dates</h2><p>Dates are stored on this policy, not the conversation.</p></div><CalendarClock :size="17" color="#71889a" /></div>
          <div class="form-grid" style="row-gap:18px">
            <div><span class="eyebrow">COVERAGE START</span><div style="font-size:12px;font-weight:700;margin-top:6px">{{ formatDate(policy.coverageStart, { day:'numeric', month:'long', year:'numeric' }) }}</div></div>
            <div><span class="eyebrow">COVERAGE END</span><div style="font-size:12px;font-weight:700;margin-top:6px">{{ formatDate(policy.coverageEnd, { day:'numeric', month:'long', year:'numeric' }) }}</div></div>
            <div v-if="policy.renewalDue"><span class="eyebrow">MOTOR RENEWAL</span><div style="font-size:12px;font-weight:800;color:#a45e0a;margin-top:6px">Renewal due {{ formatDate(policy.renewalDue, { day:'numeric', month:'long' }) }}</div></div>
            <div v-if="policy.premiumDue"><span class="eyebrow">PREMIUM PAYMENT</span><div style="font-size:12px;font-weight:800;color:#a45e0a;margin-top:6px">Premium due {{ formatDate(policy.premiumDue, { day:'numeric', month:'long' }) }}</div></div>
            <div><span class="eyebrow">PREMIUM AMOUNT</span><div style="font-size:12px;font-weight:700;margin-top:6px">{{ formatMoney(policy.premiumAmount) }}</div></div>
          </div>
        </section>
      </div>
      <aside class="detail-aside">
        <section class="card card-pad">
          <div class="section-heading"><h2>Private insurer filing reference</h2><LockKeyhole :size="16" color="#778b9c" /></div>
          <div class="private-reference"><div><span>For lookup in existing insurer records only</span><strong>{{ revealReference ? policy.filingReference : '••••••••••••••••••' }}</strong></div><button class="icon-button-small" :aria-label="revealReference ? 'Hide filing reference' : 'Show filing reference'" @click="revealReference = !revealReference">{{ revealReference ? 'Hide' : 'Show' }}</button></div>
          <p class="field-hint">This is a private filing note—not a new insurer integration or a customer-facing policy number.</p>
        </section>
        <section class="card card-pad">
          <div class="section-heading"><div><h2>Client</h2><p>Policy owner</p></div></div>
          <RouterLink :to="`/clients/${client.id}`" class="policy-mini" style="padding:0;border:0"><span class="policy-icon"><ShieldCheck :size="18" /></span><span class="policy-mini-copy"><strong>{{ client.name }}</strong><span>{{ client.reference }}</span></span><ArrowUpRight :size="15" color="#7890a2" /></RouterLink>
          <RouterLink :to="`/log/${client.id}?policy=${policy.id}`" class="button button-primary full-width" style="margin-top:14px"><FileText :size="15" /> Log contact about policy</RouterLink>
        </section>
        <section v-if="nextCallback" class="card next-callback-card">
          <span class="eyebrow">NEXT PROMISED CALLBACK</span><div class="callback-date-large" style="margin-top:10px">{{ friendlyCallback(nextCallback.date, nextCallback.time, state.demoDate) }}</div>
          <h2>{{ nextCallback.reason }}</h2><p>Next action: call {{ client.nickname || client.name }}.</p>
        </section>
      </aside>
    </div>
  </div>
  <section v-else class="card card-pad"><EmptyState title="Policy not found" description="This policy may have been removed when the demo was reset." action-label="Back to clients" @action="router.push('/clients')" /></section>
</template>
