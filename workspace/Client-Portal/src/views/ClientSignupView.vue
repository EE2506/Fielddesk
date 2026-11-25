<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { connectClientProfile, DEMO_AGENT_CODE, startSampleClient } from '../stores/clientPortal'

const router = useRouter()
const form = reactive({ fullName: '', mobile: '', email: '', agentCode: '' })
const errors = reactive({ fullName: '', mobile: '', email: '', agentCode: '' })
const submitError = ref('')

function normalizePhilippineMobile(value: string) {
  let digits = value.replace(/\D/g, '')
  if (digits.startsWith('63')) digits = digits.slice(2)
  else if (digits.startsWith('0')) digits = digits.slice(1)
  return digits.length === 10 ? `+63${digits}` : ''
}

function submit() {
  errors.fullName = ''
  errors.mobile = ''
  errors.email = ''
  errors.agentCode = ''
  submitError.value = ''

  const name = form.fullName.trim()
  const mobile = normalizePhilippineMobile(form.mobile)
  const code = form.agentCode.trim().toUpperCase()
  if (name.length < 2) errors.fullName = 'Enter your name so your agent knows who is connecting.'
  if (!mobile) errors.mobile = 'Enter a valid 10-digit Philippine mobile number after +63.'
  if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) errors.email = 'Enter a valid email address or leave this optional field blank.'
  if (!code) errors.agentCode = 'Enter the invite code from your insurance agent.'
  else if (code !== DEMO_AGENT_CODE) errors.agentCode = 'That demo code was not recognized. For this prototype, use ANA-4821.'

  if (Object.values(errors).some(Boolean)) return
  connectClientProfile({ fullName: name, mobile, email: form.email.trim(), agentCode: code })
  router.push('/client/dashboard')
}

function useSampleAccount() {
  startSampleClient()
  router.push('/client/dashboard')
}
</script>

<template>
  <div class="portal-page portal-signup-page">
    <header class="portal-topbar portal-frame">
      <RouterLink to="/client/signup" class="portal-brand" aria-label="Fielddesk client portal home">
        <span class="portal-brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 2.8 20 6v5.6c0 5.3-3.3 8.3-8 10.6-4.7-2.3-8-5.3-8-10.6V6l8-3.2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m8.3 12.1 2.3 2.3 5.1-5.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        <span><strong>Fielddesk</strong><small>CLIENT PORTAL</small></span>
      </RouterLink>
      <RouterLink to="/" class="portal-switch-link">Agent workspace <span aria-hidden="true">↗</span></RouterLink>
    </header>

    <main class="portal-frame portal-signup-layout">
      <section class="portal-story">
        <p class="portal-eyebrow"><span></span> YOUR INSURANCE, WITH YOUR AGENT</p>
        <h1>A clearer place to stay connected.</h1>
        <p class="portal-story-intro">See your upcoming policy dates and reach your insurance agent from one simple client dashboard.</p>
        <div class="portal-agent-preview">
          <div class="portal-agent-avatar">AR</div>
          <div class="portal-agent-preview-copy"><span>YOUR AGENT IN THIS DEMO</span><strong>Ana Reyes</strong><small>Independent insurance agent</small></div>
          <span class="portal-connected-mark" aria-label="Demo agent available">✓</span>
        </div>
        <div class="portal-code-card"><span>DEMO INVITE CODE</span><strong>ANA-4821</strong><small>Use this fictional code to preview connecting to Ana.</small></div>
        <ul class="portal-benefit-list">
          <li><span>01</span> Keep policy dates in view</li>
          <li><span>02</span> Know who to contact with a question</li>
          <li><span>03</span> See your agent connection in one place</li>
        </ul>
      </section>

      <section class="portal-signup-card" aria-labelledby="signup-title">
        <div class="portal-card-kicker">CLIENT SIGN-UP · PROTOTYPE</div>
        <h2 id="signup-title">Connect with your agent</h2>
        <p class="portal-signup-lede">Enter a few details and the invite code your agent shared with you.</p>
        <form class="portal-form" @submit.prevent="submit" novalidate>
          <div class="portal-form-field">
            <label for="client-name">Full name <span>Required</span></label>
            <input id="client-name" v-model="form.fullName" type="text" autocomplete="name" placeholder="e.g. Mila Santos" :aria-invalid="!!errors.fullName" aria-describedby="name-error" required>
            <small v-if="errors.fullName" id="name-error" class="portal-field-error" role="alert">{{ errors.fullName }}</small>
          </div>
          <div class="portal-form-field">
            <label for="client-mobile">Philippine mobile number <span>Required</span></label>
            <div class="portal-phone-entry"><span aria-hidden="true">+63</span><input id="client-mobile" v-model="form.mobile" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="917 555 4821" :aria-invalid="!!errors.mobile" aria-describedby="mobile-hint mobile-error" required></div>
            <small id="mobile-hint" class="portal-field-hint">Enter the 10 digits after +63. Use fictional details in this demo.</small>
            <small v-if="errors.mobile" id="mobile-error" class="portal-field-error" role="alert">{{ errors.mobile }}</small>
          </div>
          <div class="portal-form-field">
            <label for="client-email">Email <span>Optional</span></label>
            <input id="client-email" v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com" :aria-invalid="!!errors.email" aria-describedby="email-error">
            <small v-if="errors.email" id="email-error" class="portal-field-error" role="alert">{{ errors.email }}</small>
          </div>
          <div class="portal-form-field">
            <label for="agent-code">Agent invite code <span>Required</span></label>
            <input id="agent-code" v-model="form.agentCode" type="text" autocapitalize="characters" autocomplete="off" placeholder="Enter your agent’s code" :aria-invalid="!!errors.agentCode" aria-describedby="code-hint code-error" required>
            <small id="code-hint" class="portal-field-hint">Demo code: <button class="portal-inline-button" type="button" @click="form.agentCode = DEMO_AGENT_CODE">Use ANA-4821</button></small>
            <small v-if="errors.agentCode" id="code-error" class="portal-field-error" role="alert">{{ errors.agentCode }}</small>
          </div>
          <p v-if="submitError" class="portal-field-error" role="alert">{{ submitError }}</p>
          <button class="portal-button portal-button-primary portal-submit" type="submit">Create my demo profile <span aria-hidden="true">→</span></button>
        </form>
        <div class="portal-or"><span></span><small>OR</small><span></span></div>
        <button class="portal-button portal-button-secondary portal-sample-button" type="button" @click="useSampleAccount">Skip sign-up · view sample dashboard</button>
        <p class="portal-privacy-note"><strong>Prototype only.</strong> Nothing is sent or saved. No account verification is connected; please use fictional details.</p>
      </section>
    </main>
    <footer class="portal-footer portal-frame"><span>Fielddesk · Client experience concept</span><span>Sample data only · No real messages or calls</span></footer>
  </div>
</template>
