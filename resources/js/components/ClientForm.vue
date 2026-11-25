<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormErrors from './FormErrors.vue'
import { clientUrl, type Client } from '../workspace'

const props = defineProps<{ client?: Client }>()
const form = useForm({
  name: props.client?.name ?? '',
  nickname: props.client?.nickname ?? '',
  mobile: props.client?.mobile ?? '',
  shared_mobile: false,
})

function save() {
  if (props.client) {
    form.patch(clientUrl(props.client.id), { preserveScroll: true })
  } else {
    form.post('/workspace/clients')
  }
}
</script>

<template>
  <form class="fields" @submit.prevent="save">
    <label>Mobile number
      <input v-model="form.mobile" type="tel" autocomplete="off" maxlength="30" required :aria-invalid="Boolean(form.errors.mobile)">
    </label>
    <label>Name <input v-model="form.name" maxlength="120"></label>
    <label>Nickname <input v-model="form.nickname" maxlength="80"></label>
    <label v-if="form.errors.shared_mobile || form.shared_mobile" class="check">
      <input v-model="form.shared_mobile" type="checkbox"> This is a different client who shares the number
    </label>
    <FormErrors :errors="form.errors" />
    <button class="primary" :disabled="form.processing">{{ client ? 'Save client' : 'Add client' }}</button>
  </form>
</template>
