<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormErrors from '../../resources/js/components/FormErrors.vue'
import WorkspaceLayout from '../../resources/js/components/WorkspaceLayout.vue'

const props = defineProps<{ account: { name: string; policy_notice_days: number; accepts_introductions: boolean } }>()
const form = useForm({
  policy_notice_days: props.account.policy_notice_days,
  accepts_introductions: props.account.accepts_introductions,
})
</script>

<template>
  <WorkspaceLayout title="Settings">
    <header class="page-title"><h1>Workspace settings</h1><p>{{ account.name }}</p></header>
    <section class="panel settings-panel">
      <h2>Reminders and introductions</h2>
      <form class="fields" @submit.prevent="form.patch('/workspace/settings')">
        <label>Highlight policy deadlines this many days ahead
          <input v-model.number="form.policy_notice_days" type="number" min="1" max="30" required>
        </label>
        <label class="check"><input v-model="form.accepts_introductions" type="checkbox"> Show my name to clients looking for an agent</label>
        <FormErrors :errors="form.errors" />
        <button class="primary" :disabled="form.processing">Save settings</button>
        <p v-if="form.recentlySuccessful" role="status">Settings saved.</p>
      </form>
    </section>
  </WorkspaceLayout>
</template>
