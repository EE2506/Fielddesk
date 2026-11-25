<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '../components/AuthLayout.vue'

const form = useForm({ name: '', email: '', role: '', password: '', password_confirmation: '' })
</script>

<template>
  <AuthLayout title="Create your account">
    <form class="fields auth-fields" method="post" @submit.prevent="form.post('/register', { onFinish: () => form.reset('password', 'password_confirmation') })">
      <fieldset class="account-types" :aria-describedby="form.errors.role ? 'register-role-error' : undefined">
        <legend>How will you use Fielddesk?</legend>
        <div class="role-options">
          <label class="role-option">
            <input v-model="form.role" name="role" type="radio" value="agent" required>
            <span><strong>I’m an insurance agent</strong><small>Manage clients, deadlines, and callbacks</small></span>
          </label>
          <label class="role-option">
            <input v-model="form.role" name="role" type="radio" value="client" required>
            <span><strong>I want to find an agent</strong><small>Request an introduction to an available agent</small></span>
          </label>
        </div>
        <p v-if="form.errors.role" id="register-role-error" class="field-error" role="alert">{{ form.errors.role }}</p>
      </fieldset>
      <label for="register-name">Full name</label>
      <input id="register-name" v-model="form.name" name="name" autocomplete="name" maxlength="120" required
        :aria-invalid="Boolean(form.errors.name)" :aria-describedby="form.errors.name ? 'register-name-error' : undefined">
      <p v-if="form.errors.name" id="register-name-error" class="field-error" role="alert">{{ form.errors.name }}</p>
      <label for="register-email">Email address</label>
      <input id="register-email" v-model="form.email" name="email" type="email" autocomplete="username" maxlength="254" required
        :aria-invalid="Boolean(form.errors.email)" :aria-describedby="form.errors.email ? 'register-email-error' : undefined">
      <p v-if="form.errors.email" id="register-email-error" class="field-error" role="alert">{{ form.errors.email }}</p>
      <label for="register-password">Password</label>
      <input id="register-password" v-model="form.password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required
        :aria-invalid="Boolean(form.errors.password)" :aria-describedby="form.errors.password ? 'register-password-error' : 'register-password-hint'">
      <p v-if="form.errors.password" id="register-password-error" class="field-error" role="alert">{{ form.errors.password }}</p>
      <p v-else id="register-password-hint" class="muted input-note">At least 12 characters</p>
      <label for="register-confirmation">Confirm password</label>
      <input id="register-confirmation" v-model="form.password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" maxlength="72" required>
      <button class="primary" :disabled="form.processing">{{ form.processing ? 'Creating account…' : 'Create account' }}</button>
    </form>
    <p class="auth-switch">Already have an account? <Link href="/login">Sign in</Link></p>
  </AuthLayout>
</template>
