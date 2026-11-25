<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import FormErrors from './FormErrors.vue'
import { clientUrl, type Callback, type Policy } from '../workspace'

const props = defineProps<{ clientId: number; policies: Policy[]; source?: Callback }>()
const outcomes = ['Reached', 'No answer', 'Callback requested'] as const
const schedule = ref(false)
const saveFailed = ref(false)
const form = useForm({
  submission_id: '',
  source_task_id: props.source?.id ?? null,
  policy_id: props.source?.policy?.id ?? null as number | null,
  outcome: 'Reached' as typeof outcomes[number],
  note: '',
  callback_at: '',
  reason: props.source?.reason ?? '',
})
const needsCallback = computed(() => schedule.value || form.outcome === 'Callback requested')

function save() {
  saveFailed.value = false
  form.submission_id ||= crypto.randomUUID()
  form.transform((data) => ({
    ...data, callback_at: needsCallback.value ? data.callback_at : null,
    reason: needsCallback.value ? data.reason : null,
  })).post(clientUrl(props.clientId) + '/contacts', {
    preserveScroll: true,
    onNetworkError: () => {
      saveFailed.value = true
      return false
    },
    onHttpException: (response) => {
      if (response.status >= 500) {
        saveFailed.value = true
        return false
      }
    },
    onSuccess: () => {
      form.reset()
      schedule.value = false
    },
  })
}
</script>

<template>
  <form class="fields" @submit.prevent="save">
    <div class="field-pair">
      <label>Outcome
        <select v-model="form.outcome">
          <option v-for="outcome in outcomes" :key="outcome">{{ outcome }}</option>
        </select>
      </label>
      <label v-if="!source">Policy
        <select v-model="form.policy_id">
          <option :value="null">General contact</option>
          <option v-for="policy in policies" :key="policy.id" :value="policy.id">{{ policy.product }}</option>
        </select>
      </label>
      <p v-else-if="source.policy" class="muted">Policy · {{ source.policy.product }}</p>
    </div>
    <label>Contact note <textarea v-model="form.note" maxlength="500" rows="3"></textarea></label>
    <label v-if="form.outcome !== 'Callback requested'" class="check">
      <input v-model="schedule" type="checkbox"> Schedule a callback
    </label>
    <div v-if="needsCallback" class="field-pair">
      <label>Callback time in Manila <input v-model="form.callback_at" type="datetime-local" required></label>
      <label>Callback reason <input v-model="form.reason" maxlength="120" required></label>
    </div>
    <p v-if="saveFailed" class="form-errors" role="alert">The save could not be confirmed. Your draft is still here.</p>
    <FormErrors :errors="form.errors" />
    <button class="primary" :disabled="form.processing">{{ form.processing ? 'Saving…' : (saveFailed ? 'Retry save' : 'Save contact') }}</button>
  </form>
</template>
