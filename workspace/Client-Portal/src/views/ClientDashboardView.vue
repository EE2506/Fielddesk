<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { clearClientProfile, clientPortalAgent, clientPortalPolicies, clientPortalSession, ensureSampleClient, startSampleClient } from '../stores/clientPortal'
import { formatDate, formatMoney, maskMobile } from '../utils'

const router = useRouter()
if (!clientPortalSession.profile) ensureSampleClient()
const profile = computed(() => clientPortalSession.profile!)
const firstName = computed(() => profile.value.fullName.trim().split(/\s+/)[0] || 'there')
const contactMode = ref<'' | 'message' | 'call'>('')
const contactMessage = ref('')
const preferredTime = ref('Any time')
const toast = ref('')

function useSample() {
  startSampleClient()
  contactMode.value = ''
  router.push('/client/dashboard')
}
function closeAccount() {
  clearClientProfile()
  router.push('/client/signup')
}
function openMessage(policyName = '') {
  contactMessage.value = policyName ? `Hi Ana, I have a question about my ${policyName} policy.` : ''
  contactMode.value = 'message'
}
function openCallRequest() {
  preferredTime.value = 'Any time'
  contactMode.value = 'call'
}
function finishDemoAction() {
  contactMode.value = ''
  contactMessage.value = ''
  toast.value = 'Demo only: nothing was sent, called, or saved.'
  window.setTimeout(() => { toast.value = '' }, 3200)
}
</script>

<template>
  <div class="portal-page portal-dashboard-page">
    <header class="portal-topbar portal-frame">
      <RouterLink to="/client/dashboard" class="portal-brand" aria-label="Fielddesk client portal home">
        <span class="portal-brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 2.8 20 6v5.6c0 5.3-3.3 8.3-8 10.6-4.7-2.3-8-5.3-8-10.6V6l8-3.2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m8.3 12.1 2.3 2.3 5.1-5.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        <span><strong>Fielddesk</strong><small>CLIENT PORTAL</small></span>
      </RouterLink>
      <div class="portal-header-actions"><span class="portal-demo-chip">SAMPLE CLIENT</span><button class="portal-switch-link portal-switch-button" type="button" @click="closeAccount">Change account</button><RouterLink to="/" class="portal-switch-link">Agent workspace <span aria-hidden="true">↗</span></RouterLink></div>
    </header>

    <main class="portal-frame portal-dashboard-main">
      <section class="portal-dashboard-heading">
        <div><p class="portal-eyebrow"><span></span> YOUR CLIENT DASHBOARD</p><h1>Hello, {{ firstName }}.</h1><p class="portal-heading-copy">Here’s what’s coming up with your cover.</p></div>
        <div class="portal-connected-pill"><span></span>Connected to {{ clientPortalAgent.name }}</div>
      </section>

      <section class="portal-next-due" aria-label="Upcoming premium reminder">
        <div class="portal-due-mark" aria-hidden="true">!</div>
        <div class="portal-due-copy"><span>NEXT PAYMENT</span><h2>Life premium due {{ formatDate('2026-12-02') }}</h2><p>Family Protect 20 <span aria-hidden="true">·</span> {{ formatMoney(2450) }}</p></div>
        <button class="portal-button portal-button-light" type="button" @click="openMessage('Family Protect 20')">Ask about payment <span aria-hidden="true">→</span></button>
      </section>

      <div class="portal-dashboard-grid">
        <section class="portal-policy-section">
          <div class="portal-section-heading"><div><h2>Your policies</h2><p>Fictional examples from the Fielddesk prototype</p></div><span class="portal-policy-count">2 policies</span></div>
          <article v-for="policy in clientPortalPolicies" :key="policy.id" class="portal-policy-card">
            <div class="portal-policy-icon" :class="`portal-policy-icon-${policy.kind.toLowerCase()}`">{{ policy.kind === 'Life' ? 'LIFE' : 'MOTOR' }}</div>
            <div class="portal-policy-main">
              <div class="portal-policy-title-row"><div><span class="portal-policy-kind">{{ policy.kind }} INSURANCE</span><h3>{{ policy.product }}</h3></div><span class="portal-active-pill">{{ policy.status }}</span></div>
              <p class="portal-policy-number">Policy number {{ policy.policyNumber }}</p>
              <div class="portal-policy-detail-row"><span>{{ policy.details }}</span><strong>{{ policy.dueLabel }}</strong></div>
              <div class="portal-policy-actions"><span v-if="policy.amount" class="portal-policy-amount">{{ formatMoney(policy.amount) }} due</span><span v-else class="portal-policy-amount">Renewal coming up</span><button type="button" class="portal-text-button" @click="openMessage(policy.product)">Ask your agent</button></div>
            </div>
          </article>
          <p class="portal-policy-disclaimer">Policy dates and status shown here are fictional sample data. Confirm details with your agent.</p>
        </section>

        <aside class="portal-dashboard-aside">
          <section class="portal-agent-card">
            <div class="portal-section-heading"><div><h2>Your agent</h2><p>Connected through invite code {{ profile.agentCode }}</p></div><span class="portal-agent-check" aria-label="Connected">✓</span></div>
            <div class="portal-agent-profile"><span class="portal-agent-avatar">{{ clientPortalAgent.initials }}</span><div><strong>{{ clientPortalAgent.name }}</strong><span>{{ clientPortalAgent.role }}</span></div></div>
            <div class="portal-agent-phone"><span>Phone</span><strong>{{ maskMobile(clientPortalAgent.mobile) }}</strong></div>
            <div class="portal-agent-actions"><button class="portal-button portal-button-primary" type="button" @click="openMessage()">Message Ana</button><button class="portal-button portal-button-secondary" type="button" @click="openCallRequest">Request a call</button></div>
            <p class="portal-action-note">Contact buttons are simulations in this demo.</p>
          </section>

          <section class="portal-profile-card">
            <div class="portal-section-heading"><div><h2>Your profile</h2><p>Details for this demo session</p></div></div>
            <div class="portal-profile-line"><span>Name</span><strong>{{ profile.fullName }}</strong></div>
            <div class="portal-profile-line"><span>Mobile</span><strong>{{ maskMobile(profile.mobile) }}</strong></div>
            <div class="portal-profile-line"><span>Email</span><strong>{{ profile.email || 'Not added' }}</strong></div>
          </section>

          <p class="portal-privacy-card"><strong>Only client-facing details.</strong> This view does not show agent notes or private insurer filing references.</p>
        </aside>
      </div>
    </main>
    <footer class="portal-footer portal-frame"><span>Fielddesk · Client experience concept</span><span>Demo profile is held in memory only</span></footer>

    <div v-if="contactMode" class="portal-modal-backdrop" @click.self="contactMode = ''">
      <section class="portal-contact-modal" role="dialog" aria-modal="true" :aria-labelledby="contactMode === 'message' ? 'client-message-title' : 'client-call-title'">
        <button class="portal-modal-close" type="button" aria-label="Close" @click="contactMode = ''">×</button>
        <template v-if="contactMode === 'message'">
          <span class="portal-modal-kicker">DEMO MESSAGE</span><h2 id="client-message-title">Message {{ clientPortalAgent.name }}</h2><p>This is a preview of the message flow. Nothing will be sent or saved.</p>
          <label for="client-message">Your message</label><textarea id="client-message" v-model="contactMessage" maxlength="300" placeholder="What would you like to ask Ana?" rows="4"></textarea>
          <button class="portal-button portal-button-primary portal-modal-submit" type="button" :disabled="!contactMessage.trim()" @click="finishDemoAction">Send demo message <span aria-hidden="true">→</span></button>
        </template>
        <template v-else>
          <span class="portal-modal-kicker">DEMO CALL REQUEST</span><h2 id="client-call-title">Request a call</h2><p>Choose a preferred time for the prototype. This request will not be sent.</p>
          <label for="preferred-time">Preferred time</label><select id="preferred-time" v-model="preferredTime"><option>Any time</option><option>Morning</option><option>Lunch time</option><option>After work</option></select>
          <button class="portal-button portal-button-primary portal-modal-submit" type="button" @click="finishDemoAction">Send demo request <span aria-hidden="true">→</span></button>
        </template>
        <p class="portal-modal-disclaimer">Frontend prototype only · No real calls or messages</p>
      </section>
    </div>
    <Transition name="portal-toast"><div v-if="toast" class="portal-toast" role="status">{{ toast }}</div></Transition>
  </div>
</template>
