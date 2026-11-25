<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '../components/AuthLayout.vue'

const form = useForm({ email: '', password: '' })
</script>

<template>
  <AuthLayout title="Sign in to Fielddesk">
    <p class="muted">Open your agent workspace or client account.</p>
    <form class="fields auth-fields" method="post" @submit.prevent="form.post('/login', { onFinish: () => form.reset('password') })">
      <label for="login-email">Email address</label>
      <input id="login-email" v-model="form.email" name="email" type="email" autocomplete="username" maxlength="254" required
        :aria-invalid="Boolean(form.errors.email)" :aria-describedby="form.errors.email ? 'login-email-error' : undefined">
      <p v-if="form.errors.email" id="login-email-error" class="field-error" role="alert">{{ form.errors.email }}</p>
      <label for="login-password">Password</label>
      <input id="login-password" v-model="form.password" name="password" type="password" autocomplete="current-password" maxlength="72" required
        :aria-invalid="Boolean(form.errors.password)" :aria-describedby="form.errors.password ? 'login-password-error' : undefined">
      <p v-if="form.errors.password" id="login-password-error" class="field-error" role="alert">{{ form.errors.password }}</p>
      <button class="primary" :disabled="form.processing">{{ form.processing ? 'Signing in…' : 'Sign in' }}</button>
    </form>
    <p class="auth-switch">New to Fielddesk? <Link href="/register">Create an account</Link></p>
  </AuthLayout>
</template>
