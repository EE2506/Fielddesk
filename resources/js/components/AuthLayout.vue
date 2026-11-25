<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

const brandImage = '/fielddesk.svg'

defineProps<{ title: string }>()
</script>

<template>
  <Head :title="title" />
  <main class="auth-layout">
    <aside class="auth-context" aria-label="About Fielddesk">
      <a class="brand-lockup" href="/"><img :src="brandImage" width="36" height="36" alt=""> Fielddesk</a>
      <div class="auth-intro">
        <p class="auth-lead">Keep the next promise in view</p>
        <p>Pick up a client conversation with the callback, policy date, and last agreement at hand.</p>
        <div class="auth-sample" aria-label="Sample callback">
          <div class="sample-date"><strong>27</strong><span>Nov</span></div>
          <div><strong>Motor renewal callback</strong><p>Friday at 4:00 PM · Manila</p></div>
        </div>
      </div>
    </aside>
    <section class="auth-screen" aria-labelledby="auth-title">
      <a class="brand-lockup auth-mobile-brand" href="/"><img :src="brandImage" width="32" height="32" alt=""> Fielddesk</a>
      <div class="auth-card">
        <h1 id="auth-title">{{ title }}</h1>
        <slot />
        <Link href="/" class="auth-back">Back to Fielddesk</Link>
      </div>
    </section>
  </main>
</template>
