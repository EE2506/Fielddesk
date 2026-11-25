<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ArrowLeft, Save, ShieldCheck } from 'lucide-vue-next'
import { addClient, getClient, updateClient } from '../stores/workspace'
import { maskMobile } from '../utils'

const route = useRoute()
const router = useRouter()
const editing = computed(() => Boolean(route.params.clientId))
const clientId = computed(() => String(route.params.clientId || ''))
const existing = computed(() => editing.value ? getClient(clientId.value) : undefined)
const form = reactive({ name: '', nickname: '', reference: '', mobile: '', notes: '' })
const errors = ref<Record<string, string>>({})
watch(existing, client => {
  if (client) Object.assign(form, { name: client.name, nickname: client.nickname, reference: client.reference, mobile: '', notes: client.notes })
}, { immediate: true })
function validate() {
  const next: Record<string,string> = {}
  if (!form.name.trim()) next.name = 'Add the client’s name so you can find the right record.'
  if (!editing.value && !form.mobile.trim()) next.mobile = 'A mobile number is required for a new client.'
  if (form.mobile.trim() && form.mobile.replace(/\D/g, '').length < 10) next.mobile = 'Enter a valid mobile number with at least 10 digits.'
  errors.value = next
  return Object.keys(next).length === 0
}
function submit() {
  if (!validate()) return
  if (editing.value && existing.value) {
    const values: { name:string; nickname:string; notes:string; mobile?:string } = { name: form.name.trim(), nickname: form.nickname.trim(), notes: form.notes.trim() }
    if (form.mobile.trim()) values.mobile = form.mobile.replace(/\s/g, '')
    const updated = updateClient(clientId.value, values)
    if (updated) router.push(`/clients/${updated.id}`)
  } else {
    const client = addClient({ name: form.name.trim(), nickname: form.nickname.trim(), reference: form.reference.trim(), mobile: form.mobile.replace(/\s/g, ''), notes: form.notes.trim() })
    router.push(`/clients/${client.id}`)
  }
}
</script>

<template>
  <button class="back-link" @click="router.push(existing ? `/clients/${existing.id}` : '/clients')"><ArrowLeft :size="15" /> {{ editing ? 'Back to client' : 'Back to clients' }}</button>
  <div class="page-head"><div><span class="eyebrow">CLIENT RECORD · LOCAL DEMO</span><h1>{{ editing ? 'Edit client' : 'Add a client' }}</h1><p>Keep just enough detail to continue the conversation.</p></div></div>
  <form v-if="!editing || existing" class="card" novalidate @submit.prevent="submit">
    <section class="form-section"><h2>Client details</h2><p>Name helps identify the person. A mobile number is the only required contact detail; all other contact fields are optional.</p>
      <div class="form-grid">
        <div class="form-field"><label class="field-label" for="client-name">Client name <span class="required-mark">*</span></label><input id="client-name" v-model="form.name" class="input" maxlength="80" placeholder="e.g. Mila Santos" autocomplete="name" :aria-invalid="Boolean(errors.name)" /><div v-if="errors.name" class="field-error">{{ errors.name }}</div></div>
        <div class="form-field"><label class="field-label" for="client-nickname">Nickname <span style="color:#8495a3;font-weight:400">· optional</span></label><input id="client-nickname" v-model="form.nickname" class="input" maxlength="40" placeholder="e.g. Mila" /><span class="field-hint">Useful for a quick search.</span></div>
        <div class="form-field"><label class="field-label" for="client-mobile">Mobile number <span class="required-mark">{{ editing ? '' : '*' }}</span><span v-if="editing" style="color:#8495a3;font-weight:400">· optional to change</span></label><input id="client-mobile" v-model="form.mobile" class="input" type="tel" inputmode="tel" maxlength="24" placeholder="+63 917 000 0000" autocomplete="tel" :aria-invalid="Boolean(errors.mobile)" /><div v-if="errors.mobile" class="field-error">{{ errors.mobile }}</div><span v-else-if="existing" class="field-hint">Current number: {{ maskMobile(existing.mobile) }}. Leave blank to keep it.</span><span v-else class="field-hint">Fictional demo number. Displayed masked in client records.</span></div>
        <div class="form-field"><label class="field-label" for="client-reference">Client reference <span style="color:#8495a3;font-weight:400">· optional</span></label><input id="client-reference" v-model="form.reference" class="input" maxlength="20" placeholder="Auto-generated if blank" /><span class="field-hint">Use the reference already in your records, if you have one.</span></div>
        <div class="form-field span-2"><label class="field-label" for="client-notes">Agent note <span style="color:#8495a3;font-weight:400">· optional</span></label><textarea id="client-notes" v-model="form.notes" class="textarea" maxlength="300" placeholder="A useful reminder for your next conversation"></textarea></div>
      </div>
    </section>
    <div class="form-footer"><span class="form-footer-note"><ShieldCheck :size="13" style="vertical-align:-3px;margin-right:4px" />No real calls or messages are sent.</span><div class="form-footer-actions"><RouterLink class="button button-secondary" :to="existing ? `/clients/${existing.id}` : '/clients'">Cancel</RouterLink><button class="button button-primary" type="submit"><Save :size="15" /> {{ editing ? 'Save client' : 'Add client' }}</button></div></div>
  </form>
  <section v-else class="card card-pad"><div class="empty-state"><strong>Client not found</strong><p>This demo record may have been reset.</p><RouterLink to="/clients" class="button button-secondary button-small">Back to clients</RouterLink></div></section>
</template>
