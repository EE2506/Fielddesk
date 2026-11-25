<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormErrors from './FormErrors.vue'
import { clientUrl, manilaTime, type PolicyDocument } from '../workspace'

const props = defineProps<{ clientId: number; policyId: number; documents: PolicyDocument[] }>()
const statuses = ['Missing', 'Received', 'Submitted'] as const
const url = clientUrl(props.clientId) + '/policies/' + props.policyId + '/documents'
const add = useForm({ name: '' })
const change = useForm<{ status: PolicyDocument['status'] }>({ status: 'Missing' })

function update(id: number, event: Event) {
  const select = event.target as HTMLSelectElement
  const next = statuses.find((status) => status === select.value)
  if (!next) return
  change.status = next
  change.patch(url + '/' + id, {
    preserveScroll: true,
    onError: () => { select.value = props.documents.find((document) => document.id === id)?.status ?? 'Missing' },
  })
}
</script>

<template>
  <h4>Requirements</h4>
  <p v-if="!documents.length" class="muted">No requirements added.</p>
  <ul v-else class="rows">
    <li v-for="document in documents" :key="document.id" class="document-row">
      <label>{{ document.name }}
        <select :value="document.status" :disabled="change.processing" @change="update(document.id, $event)">
          <option v-for="status in statuses" :key="status">{{ status }}</option>
        </select>
      </label>
      <p class="muted">Updated {{ manilaTime(document.updated_at) }}</p>
    </li>
  </ul>
  <FormErrors :errors="change.errors" />
  <form class="search-form" @submit.prevent="add.post(url, { preserveScroll: true, onSuccess: () => add.reset() })">
    <label>Requirement name <input v-model="add.name" maxlength="120" required></label>
    <button :disabled="add.processing">Add requirement</button>
  </form>
  <FormErrors :errors="add.errors" />
</template>
