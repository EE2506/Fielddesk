<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { AlarmClock, ArrowRight, CalendarClock, CalendarDays, Check, ChevronRight, Clock3, RotateCcw, Shield, Sparkles } from 'lucide-vue-next'
import { useWorkspace, getClient, getPolicy, showSimulatedAction, advanceToFriday, returnToWednesday, resetDemo, completeCallback } from '../stores/workspace'
import { daysBetween, formatDate, formatLongDate, formatMoney } from '../utils'
import EmptyState from './EmptyState.vue'

const { state } = useWorkspace()
const router = useRouter()
const isFriday = computed(() => state.demoDate === '2026-11-27')
const overdue = computed(() => state.callbacks.filter(item => item.status === 'Scheduled' && item.date < state.demoDate).sort((a,b) => `${a.date}${a.time}`.localeCompare(`${b.date}${b.time}`)))
const todayCalls = computed(() => state.callbacks.filter(item => item.status === 'Scheduled' && item.date === state.demoDate).sort((a,b) => a.time.localeCompare(b.time)))
const upcomingCalls = computed(() => state.callbacks.filter(item => item.status === 'Scheduled' && item.date > state.demoDate).sort((a,b) => `${a.date}${a.time}`.localeCompare(`${b.date}${b.time}`)))
const deadlines = computed(() => state.policies.map(policy => ({ policy, due: policy.premiumDue || policy.renewalDue, client: getClient(policy.clientId) }))
  .filter(item => item.due && item.client && item.due >= state.demoDate)
  .sort((a,b) => (a.due || '').localeCompare(b.due || '')))
const dueSoonCount = computed(() => deadlines.value.filter(item => daysBetween(state.demoDate, item.due || '') <= 7).length)
function callClient(clientId: string) { const client = getClient(clientId); if (client) showSimulatedAction('call', client) }
function dateDue(value: string) { const days = daysBetween(state.demoDate, value); return days === 0 ? 'Due today' : days === 1 ? 'Due tomorrow' : `Due in ${days} days` }
function complete(id: string) { completeCallback(id) }
function friendlyDay(value: string) {
  const delta = daysBetween(state.demoDate, value)
  if (delta === 0) return 'Today'
  if (delta === 1) return 'Tomorrow'
  return new Date(`${value}T12:00:00`).toLocaleDateString('en-PH', { weekday: 'long' })
}
</script>

<template>
  <div class="dashboard-intro">
    <div>
      <span class="eyebrow">{{ isFriday ? 'FRIDAY WORKSPACE · MANILA' : 'WEDNESDAY WORKSPACE · MANILA' }}</span>
      <h1>{{ isFriday ? 'Friday, let’s follow through.' : 'Good morning, Ana.' }}</h1>
      <p>{{ formatLongDate(state.demoDate) }} <span aria-hidden="true">·</span> Here’s who’s next and why.</p>
    </div>
    <div class="demo-controls" aria-label="Demo date controls">
      <div class="demo-date-chip"><span class="status-dot"></span>{{ formatDate(state.demoDate, { weekday: 'short', day: 'numeric', month: 'short' }) }} demo</div>
      <button v-if="!isFriday" class="demo-advance" @click="advanceToFriday"><CalendarDays :size="14" /> Advance to Friday <ArrowRight :size="13" /></button>
      <button v-else class="demo-advance" @click="returnToWednesday"><RotateCcw :size="13" /> Back to Wednesday</button>
      <button class="demo-reset" @click="resetDemo">Reset demo</button>
    </div>
  </div>

  <div class="summary-strip">
    <div class="summary-message"><span class="summary-icon"><AlarmClock :size="16" /></span><span><strong>{{ overdue.length ? `${overdue.length} overdue callback${overdue.length > 1 ? 's' : ''}` : 'You’re up to date' }}</strong> · {{ todayCalls.length }} call{{ todayCalls.length === 1 ? '' : 's' }} scheduled today</span></div>
    <div class="summary-right">{{ dueSoonCount }} policy deadline{{ dueSoonCount === 1 ? '' : 's' }} in the next 7 days</div>
  </div>

  <div class="today-grid">
    <div class="dashboard-left">
      <section class="card priority-card">
        <div class="section-heading">
          <div class="section-title-with-icon"><span class="section-icon"><Clock3 :size="16" /></span><div><h2>Overdue callbacks</h2><p>Start with the promise that’s waiting</p></div></div>
          <span v-if="overdue.length" class="urgent-count">{{ overdue.length }}</span>
          <span v-else class="pill pill-success"><Check :size="12" /> Clear</span>
        </div>
        <div v-if="overdue.length" class="callback-list">
          <div v-for="item in overdue" :key="item.id" class="callback-row">
            <div class="callback-time"><strong>{{ formatDate(item.date) }}</strong><small>Overdue</small></div>
            <div class="callback-details">
              <div class="client-line"><RouterLink :to="`/clients/${item.clientId}`"><strong>{{ getClient(item.clientId)?.name }}</strong></RouterLink><span class="pill pill-critical">Needs attention</span></div>
              <div class="policy-line">{{ getPolicy(item.policyId)?.kind }} · {{ getPolicy(item.policyId)?.product }}</div>
              <div class="reason-line">Why: {{ item.reason }}</div>
            </div>
            <div class="callback-actions"><button class="button button-secondary button-small" @click="callClient(item.clientId)">Call {{ getClient(item.clientId)?.nickname || getClient(item.clientId)?.name }}</button><button class="text-link" @click="complete(item.id)">Mark done</button></div>
          </div>
        </div>
        <div v-else class="dashboard-empty"><EmptyState title="No overdue callbacks" description="Promises that are past due will appear here, clearly marked." /></div>
      </section>

      <section class="card priority-card">
        <div class="section-heading">
          <div class="section-title-with-icon"><span class="section-icon"><CalendarClock :size="16" /></span><div><h2>Today’s calls</h2><p>Who to call and what to pick up</p></div></div>
          <span class="pill pill-info">{{ formatDate(state.demoDate, { weekday: 'short', day: 'numeric', month: 'short' }) }}</span>
        </div>
        <div v-if="todayCalls.length" class="callback-list">
          <div v-for="item in todayCalls" :key="item.id" class="callback-row">
            <div class="callback-time"><strong>{{ new Date(`2026-01-01T${item.time}`).toLocaleTimeString('en-PH',{hour:'numeric',minute:'2-digit'}) }}</strong><small>{{ friendlyDay(item.date) }}</small></div>
            <div class="callback-details">
              <div class="client-line"><RouterLink :to="`/clients/${item.clientId}`"><strong>{{ getClient(item.clientId)?.name }}</strong></RouterLink><span class="pill pill-warning">Call today</span></div>
              <div class="policy-line">{{ getPolicy(item.policyId)?.kind }} · {{ getPolicy(item.policyId)?.product }}</div>
              <div class="reason-line">Why: {{ item.reason }}</div>
            </div>
            <div class="callback-actions"><span class="action-label">Next: Call {{ getClient(item.clientId)?.nickname || 'client' }}</span><button class="button button-primary button-small" @click="callClient(item.clientId)">Call now</button></div>
          </div>
        </div>
        <div v-else class="dashboard-empty"><EmptyState title="No calls scheduled today" description="You can add a quick promise from any client’s detail page." action-label="Find a client" @action="router.push('/clients')" /></div>
      </section>

      <section class="card priority-card">
        <div class="section-heading">
          <div class="section-title-with-icon"><span class="section-icon"><Sparkles :size="16" /></span><div><h2>Upcoming callbacks</h2><p>Promises to keep after today</p></div></div>
          <span class="pill pill-neutral">{{ upcomingCalls.length }} scheduled</span>
        </div>
        <div v-if="upcomingCalls.length" class="callback-list">
          <div v-for="item in upcomingCalls" :key="item.id" class="callback-row">
            <div class="callback-time"><strong>{{ formatDate(item.date) }}</strong><small>{{ friendlyDay(item.date) }}</small></div>
            <div class="callback-details">
              <div class="client-line"><RouterLink :to="`/clients/${item.clientId}`"><strong>{{ getClient(item.clientId)?.name }}</strong></RouterLink><span class="pill pill-info">{{ friendlyDay(item.date) }} call</span></div>
              <div class="policy-line">{{ getPolicy(item.policyId)?.kind }} · {{ getPolicy(item.policyId)?.product }}</div>
              <div class="reason-line">Why: {{ item.reason }}</div>
            </div>
            <div class="callback-actions"><span class="action-label">Next: Call {{ friendlyDay(item.date) }}</span><button class="button button-secondary button-small" @click="router.push(`/clients/${item.clientId}`)">Open client</button></div>
          </div>
        </div>
        <div v-else class="dashboard-empty"><EmptyState title="No future calls promised yet" description="When you log a conversation and set a callback, the promise will show here on Wednesday—and move into Today’s calls on Friday." /></div>
      </section>
    </div>

    <div class="dashboard-right">
      <section class="card priority-card">
        <div class="section-heading">
          <div class="section-title-with-icon"><span class="section-icon"><Shield :size="16" /></span><div><h2>Upcoming policy deadlines</h2><p>Due dates stay tied to the policy</p></div></div>
          <span class="pill pill-warning">{{ deadlines.length }} dates</span>
        </div>
        <div v-if="deadlines.length" class="policy-deadline-list">
          <RouterLink v-for="item in deadlines" :key="item.policy.id" :to="`/policies/${item.policy.id}`" class="deadline-row">
            <div class="deadline-date"><strong>{{ new Date(`${item.due}T12:00:00`).getDate() }}</strong><span>{{ new Date(`${item.due}T12:00:00`).toLocaleDateString('en-PH',{month:'short'}) }}</span></div>
            <div class="deadline-info"><strong>{{ item.client?.name }} · {{ item.policy.kind }}</strong><span>{{ item.policy.kind === 'Motor' ? item.policy.vehicle : item.policy.product }} · {{ item.policy.kind === 'Motor' ? 'Renewal' : 'Premium' }}</span><span>Why: {{ item.policy.kind === 'Motor' ? 'renewal date is approaching' : 'premium payment is due' }}</span></div>
            <div class="deadline-tail"><strong>{{ formatMoney(item.policy.premiumAmount) }}</strong><span :class="['pill', daysBetween(state.demoDate, item.due || '') <= 3 ? 'pill-critical' : 'pill-warning']">{{ dateDue(item.due || '') }}</span><span class="action-label">Next: Call {{ item.client?.nickname || 'client' }}</span></div>
          </RouterLink>
        </div>
        <div v-else class="dashboard-empty"><EmptyState title="No upcoming deadlines" description="Policy dates will appear here as they approach." /></div>
      </section>

      <section v-if="upcomingCalls.some(item => item.clientId === 'mila')" class="card callback-spotlight">
        <div class="section-heading"><div><span class="eyebrow">PROMISE RECORDED</span><h2 style="margin-top:5px">Mila’s next call</h2></div><span class="pill pill-success"><Check :size="12" /> Saved</span></div>
        <div v-for="item in upcomingCalls.filter(item => item.clientId === 'mila')" :key="item.id" class="spotlight-row">
          <div class="spotlight-copy"><strong>“{{ item.reason }}”</strong><p>{{ getPolicy(item.policyId)?.kind }} · {{ getPolicy(item.policyId)?.product }}<br>Why: follow up on your last conversation.</p></div>
          <div class="spotlight-time">{{ friendlyDay(item.date) }}<br>{{ new Date(`2026-01-01T${item.time}`).toLocaleTimeString('en-PH',{hour:'numeric',minute:'2-digit'}) }}</div>
        </div>
      </section>
      <section v-else class="card card-pad callback-spotlight">
        <div class="section-heading"><div class="section-title-with-icon"><span class="section-icon"><Sparkles :size="16" /></span><div><h2>Keep the promise visible</h2><p>Write down the next step before your next call.</p></div></div></div>
        <RouterLink class="button button-soft button-small" to="/clients/mila">Open Mila’s details <ChevronRight :size="14" /></RouterLink>
      </section>

      <div class="card card-pad"><span class="eyebrow">QUICK LOOKUP</span><h2 style="font-size:13px;margin:8px 0 6px">Need a client’s last note?</h2><p style="font-size:10px;line-height:1.6;color:#778b9b;margin:0 0 12px">Search by mobile number, nickname or client reference.</p><RouterLink to="/clients" class="button button-secondary button-small">Find a client <ChevronRight :size="14" /></RouterLink></div>
    </div>
  </div>
</template>
