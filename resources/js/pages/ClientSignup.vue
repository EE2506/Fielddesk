<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import ClientPortalLayout from '../components/ClientPortalLayout.vue'

const form = useForm({
  name: '',
  email: '',
  role: 'client',
  password: '',
  password_confirmation: '',
})

function register() {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <ClientPortalLayout title="Client sign-up" main-class="portal-signup-layout">
    <section class="portal-story">
      <p class="portal-eyebrow"><span aria-hidden="true"></span> Your insurance, with your agent</p>
      <h1>A clearer place to stay connected.</h1>
      <p class="portal-story-intro">Create a client account to find an insurance agent and see when they respond to your introduction request.</p>
      <div class="portal-agent-preview">
        <div class="portal-agent-avatar" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
            <circle cx="12" cy="8" r="3" /><path d="M5 20v-2a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v2" />
          </svg>
        </div>
        <div class="portal-agent-preview-copy"><span>Your account</span><strong>Client portal</strong><small>Introductions and request status</small></div>
      </div>
      <div class="portal-code-card"><strong>After you sign up</strong><p>Choose an agent who is open to introductions.</p></div>
    </section>

    <section class="portal-signup-card" aria-labelledby="signup-title">
      <p class="portal-card-kicker">Client sign-up</p>
      <h2 id="signup-title">Create your client account</h2>
      <p class="portal-signup-lede">Enter your name, email address, and a password.</p>
      <form class="portal-form" method="post" @submit.prevent="register">
        <div class="portal-form-field">
          <label for="client-signup-name">Full name</label>
          <input id="client-signup-name" v-model="form.name" name="name" autocomplete="name" maxlength="120" required
            :aria-invalid="Boolean(form.errors.name)" :aria-describedby="form.errors.name ? 'client-signup-name-error' : undefined">
          <p v-if="form.errors.name" id="client-signup-name-error" class="portal-field-error" role="alert">{{ form.errors.name }}</p>
        </div>
        <div class="portal-form-field">
          <label for="client-signup-email">Email address</label>
          <input id="client-signup-email" v-model="form.email" name="email" type="email" autocomplete="username" maxlength="254" required
            :aria-invalid="Boolean(form.errors.email)" :aria-describedby="form.errors.email ? 'client-signup-email-error' : undefined">
          <p v-if="form.errors.email" id="client-signup-email-error" class="portal-field-error" role="alert">{{ form.errors.email }}</p>
        </div>
        <div class="portal-form-field">
          <label for="client-signup-password">Password</label>
          <input id="client-signup-password" v-model="form.password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required
            :aria-invalid="Boolean(form.errors.password)" :aria-describedby="form.errors.password ? 'client-signup-password-error' : 'client-signup-password-hint'">
          <p v-if="form.errors.password" id="client-signup-password-error" class="portal-field-error" role="alert">{{ form.errors.password }}</p>
          <p v-else id="client-signup-password-hint" class="portal-field-hint">At least 12 characters</p>
        </div>
        <div class="portal-form-field">
          <label for="client-signup-confirmation">Confirm password</label>
          <input id="client-signup-confirmation" v-model="form.password_confirmation" name="password_confirmation" type="password"
            autocomplete="new-password" maxlength="72" required>
        </div>
        <p v-if="form.errors.role" class="portal-field-error" role="alert">{{ form.errors.role }}</p>
        <button class="portal-button portal-button-primary portal-submit" type="submit" :disabled="form.processing">
          {{ form.processing ? 'Creating account…' : 'Create client account' }} <span aria-hidden="true">→</span>
        </button>
      </form>
      <div class="portal-or" aria-hidden="true"><span></span><small>OR</small><span></span></div>
      <Link href="/login" class="portal-button portal-button-secondary portal-signin">Sign in to an existing account</Link>
      <p class="portal-privacy-note">When an agent accepts your introduction request, they can see your email address.</p>
    </section>
  </ClientPortalLayout>
</template>
