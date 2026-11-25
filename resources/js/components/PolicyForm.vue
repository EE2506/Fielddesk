<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormErrors from './FormErrors.vue'
import { clientUrl, type Policy } from '../workspace'

const props = defineProps<{ clientId: number; policy?: Policy }>()
const form = useForm({
  insurer: props.policy?.insurer ?? '',
  product: props.policy?.product ?? '',
  reference: props.policy?.reference ?? '',
  filing_reference: props.policy?.filing_reference ?? '',
  event_label: props.policy?.event_label ?? '',
  due_on: props.policy?.due_on ?? '',
})

function save() {
  const url = clientUrl(props.clientId) + '/policies'
  if (props.policy) {
    form.patch(url + '/' + props.policy.id, { preserveScroll: true })
  } else {
    form.post(url, { preserveScroll: true, onSuccess: () => form.reset() })
  }
}
</script>

<template>
  <form class="fields" @submit.prevent="save">
    <div class="field-pair">
      <label>Insurer <input v-model="form.insurer" maxlength="120" required></label>
      <label>Product <input v-model="form.product" maxlength="120" required></label>
      <label>Policy reference <input v-model="form.reference" maxlength="120"></label>
      <label>Private filing reference <input v-model="form.filing_reference" maxlength="160"></label>
      <label>Deadline type
        <input v-model="form.event_label" list="event-types" maxlength="80" required>
      </label>
      <label>Due date <input v-model="form.due_on" type="date" required></label>
    </div>
    <FormErrors :errors="form.errors" />
    <button class="primary" :disabled="form.processing">{{ policy ? 'Save policy' : 'Add policy' }}</button>
  </form>
</template>
