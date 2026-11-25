<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { AlertTriangle, ArrowLeft, CalendarClock, Check, Clock3, FileText, Phone, RotateCcw, Save, ShieldCheck, X } from 'lucide-vue-next'
import { getCallback, getClient, getContact, getPoliciesForClient, getPolicy, notify, saveContact, setNextSaveToFail, state } from '../stores/workspace'
import { formatDate } from '../utils'
import type { ContactDraft, ContactOutcome } from '../models'
import EmptyState from './EmptyState.vue'

const route = useRoute()
const router = useRouter()
const clientId = computed(() => String(route.params.clientId || ''))
const contactId = computed(() => String(route.params.contactId || ''))
const editing = computed(() => Boolean(contactId.value))
const client = computed(() => getClient(clientId.value))
const policies = computed(() => getPoliciesForClient(clientId.value))
const currentContact = computed(() => editing.value ? getContact(contactId.value) : undefined)
const form = reactive<ContactDraft>({ clientId: '', policyId: '', outcome: 'Reached', note: '', scheduleCallback: true, callbackDate: '2026-11-27', callbackTime: '16:00', callbackReason: 'Follow up on motor renewal quote' })
const errors = ref<Record<string,string>>({})
const saveFailed = ref(false)
const failureArmed = ref(false)
const outcomes: Array<{value:ContactOutcome; label:string; hint:string}> = [
  { value:'Reached', label:'Reached', hint:'Spoke with client' },
  { value:'No answer', label:'No answer', hint:'No pick-up' },
  { value:'Left message', label:'Left message', hint:'Left a message' },
  { value:'Text sent', label:'Text sent', hint:'Sent a text' },
]
watch([client, currentContact, () => route.query.policy], ([clientValue, contact, policyQuery]) => {
  if (!clientValue) return
  if (contact) {
    const callback = contact.callbackId ? getCallback(contact.callbackId) : undefined
    Object.assign(form, {
      clientId: clientValue.id, policyId: contact.policyId || '', outcome: contact.outcome, note: contact.note,
      scheduleCallback: Boolean(callback), callbackDate: callback?.date || '2026-11-27', callbackTime: callback?.time || '16:00', callbackReason: callback?.reason || 'Follow up on our conversation',
    })
  } else {
    const selected = typeof policyQuery === 'string' && policies.value.some(policy => policy.id === policyQuery) ? policyQuery : policies.value[0]?.id || ''
    const isMilaMotor = clientValue.id === 'mila' && (getPolicy(selected)?.kind === 'Motor' || !selected)
    Object.assign(form, {
      clientId: clientValue.id, policyId: selected, outcome: 'Reached',
      note: isMilaMotor ? 'Ma’am, Friday na lang po, mga 4 PM.' : '',
      scheduleCallback: isMilaMotor, callbackDate: '2026-11-27', callbackTime: '16:00',
      callbackReason: isMilaMotor ? 'Follow up on motor renewal quote' : 'Follow up on our conversation',
    })
  }
  errors.value = {}
  saveFailed.value = false
}, { immediate: true })
function chooseOutcome(value: ContactOutcome) { form.outcome = value }
function validate() {
  const next: Record<string,string> = {}
  if (!form.outcome) next.outcome = 'Choose what happened on the call or message.'
  if (form.note.length > 500) next.note = 'Keep the note under 500 characters.'
  if (form.scheduleCallback) {
    if (!form.callbackDate) next.callbackDate = 'Choose a callback date.'
    else if (form.callbackDate < state.demoDate) next.callbackDate = `Choose ${state.demoDate} or a future date.`
    if (!form.callbackTime) next.callbackTime = 'Choose a callback time.'
    if (form.callbackReason.length > 120) next.callbackReason = 'Keep the callback reason under 120 characters.'
  }
  errors.value = next
  return Object.keys(next).length === 0
}
function submit() {
  if (!client.value || !validate()) return
  const wasEditing = editing.value
  const result = saveContact(form, wasEditing && currentContact.value ? currentContact.value.happenedOn : state.demoDate, wasEditing && currentContact.value ? currentContact.value.happenedAt : '16:02', wasEditing ? contactId.value : undefined)
  if (!result.ok) {
    saveFailed.value = true
    return
  }
  saveFailed.value = false
  const when = form.scheduleCallback ? ` · Call ${new Date(`${form.callbackDate}T12:00:00`).toLocaleDateString('en-PH',{weekday:'long'})}, ${formatDate(form.callbackDate)} at ${new Date(`2026-01-01T${form.callbackTime}`).toLocaleTimeString('en-PH',{hour:'numeric',minute:'2-digit'})}` : ''
  notify(`${wasEditing ? 'Contact updated' : 'Contact saved'}${when}`)
  router.push('/')
}
function armSaveError() { setNextSaveToFail(); failureArmed.value = true }
</script>

<template>
  <button class="back-link" @click="router.push(client ? `/clients/${client.id}` : '/clients')"><ArrowLeft :size="15" /> Back to {{ client?.nickname || 'client' }}</button>
  <div v-if="client" class="page-head"><div><span class="eyebrow">{{ editing ? 'EDIT CONVERSATION' : 'CALL / MESSAGE · LOCAL DEMO' }}</span><h1>{{ editing ? 'Edit conversation' : `Log contact with ${client.nickname || client.name}` }}</h1><p>Save what happened, then keep the next promised call easy to find.</p></div></div>
  <div v-if="client && saveFailed" class="error-banner" role="alert">
    <AlertTriangle :size="18" style="flex:none;margin-top:1px" />
    <div style="flex:1"><strong>Couldn’t save this contact yet.</strong><p>Your note is still here. Check your connection and try again; this prototype simulates the save retry.</p><div class="retry-row"><span class="field-hint" style="margin:0">Note retained: “{{ form.note || 'No note entered' }}”</span><button class="button button-secondary button-small" @click="submit"><RotateCcw :size="13" /> Retry save</button></div></div>
  </div>
  <form v-if="client" class="card" novalidate @submit.prevent="submit">
    <section class="form-section">
      <h2>What happened?</h2><p>Choose an outcome and add a short note so you know what to say next time.</p>
      <label class="field-label">Contact outcome <span class="required-mark">*</span></label>
      <div class="choice-grid" role="group" aria-label="Contact outcome">
        <button v-for="outcome in outcomes" :key="outcome.value" type="button" :class="['outcome-choice', form.outcome === outcome.value ? 'chosen' : '']" :aria-pressed="form.outcome === outcome.value" @click="chooseOutcome(outcome.value)"><Check v-if="form.outcome === outcome.value" :size="14" />{{ outcome.label }}</button>
      </div>
      <div v-if="errors.outcome" class="field-error">{{ errors.outcome }}</div>
      <div class="form-grid" style="margin-top:16px">
        <div class="form-field"><label class="field-label" for="contact-policy">Policy <span style="color:#8495a3;font-weight:400">· optional</span></label><select id="contact-policy" v-model="form.policyId" class="select"><option value="">General client conversation</option><option v-for="policy in policies" :key="policy.id" :value="policy.id">{{ policy.kind }} · {{ policy.product }}{{ (policy.renewalDue || policy.premiumDue) ? ` · due ${formatDate(policy.renewalDue || policy.premiumDue!)}` : '' }}</option></select></div>
        <div class="form-field"><label class="field-label" for="contact-note">Note from the conversation <span style="color:#8495a3;font-weight:400">· optional</span></label><textarea id="contact-note" v-model="form.note" class="textarea" maxlength="500" placeholder="What did you agree on? Add only what will help you continue the conversation." :aria-invalid="Boolean(errors.note)"></textarea><div class="note-count">{{ form.note.length }}/500</div><span v-if="errors.note" class="field-error">{{ errors.note }}</span></div>
      </div>
    </section>

    <section class="form-section">
      <label class="checkbox-row"><input v-model="form.scheduleCallback" type="checkbox" /><span><strong>Set a callback</strong><span>Turn this promise into a reminder so it appears on Today.</span></span></label>
      <div v-if="form.scheduleCallback" class="callback-schedule">
        <div style="display:flex;gap:8px;align-items:center"><span class="section-icon"><CalendarClock :size="15" /></span><div><strong style="font-size:11px">Next promised call</strong><div class="field-hint" style="margin:3px 0 0">This is an agent reminder; no message is sent.</div></div></div>
        <div class="callback-schedule-grid">
          <div class="form-field"><label class="field-label" for="callback-date">Call date <span class="required-mark">*</span></label><input id="callback-date" v-model="form.callbackDate" class="input" type="date" :min="state.demoDate" :aria-invalid="Boolean(errors.callbackDate)" /><span v-if="errors.callbackDate" class="field-error">{{ errors.callbackDate }}</span><span v-else class="field-hint">Wednesday demo → Friday, 27 Nov</span></div>
          <div class="form-field"><label class="field-label" for="callback-time">Call time <span class="required-mark">*</span></label><input id="callback-time" v-model="form.callbackTime" class="input" type="time" :aria-invalid="Boolean(errors.callbackTime)" /><span v-if="errors.callbackTime" class="field-error">{{ errors.callbackTime }}</span></div>
          <div class="form-field span-2"><label class="field-label" for="callback-reason">Why are you calling? <span style="color:#8495a3;font-weight:400">· optional</span></label><input id="callback-reason" v-model="form.callbackReason" class="input" maxlength="120" placeholder="e.g. Follow up on motor renewal quote" /><span v-if="errors.callbackReason" class="field-error">{{ errors.callbackReason }}</span></div>
        </div>
      </div>
    </section>

    <section class="form-section">
      <div class="section-heading" style="margin-bottom:6px"><div><h2>Save behavior</h2><p>Try the recovery state without losing your note.</p></div><span class="pill pill-neutral">Prototype control</span></div>
      <button type="button" class="button button-secondary button-small" :disabled="failureArmed" @click="armSaveError"><AlertTriangle :size="13" /> {{ failureArmed ? 'One save will fail' : 'Simulate a save error' }}</button>
      <span class="field-hint" style="display:block">First save will fail once. Select Retry save; your note and callback details stay in this form.</span>
    </section>

    <div class="form-footer"><span class="form-footer-note"><Phone :size="13" style="vertical-align:-3px;margin-right:4px" />{{ client.reference }} · local demo · no real contact is made</span><div class="form-footer-actions"><RouterLink class="button button-secondary" :to="`/clients/${client.id}`">Cancel</RouterLink><button class="button button-primary" type="submit"><Save :size="15" /> {{ editing ? 'Save changes' : 'Save contact' }}</button></div></div>
  </form>
  <section v-else class="card card-pad"><EmptyState title="Client not found" description="This client may have been removed by a demo reset." action-label="Find a client" @action="router.push('/clients')" /></section>
</template>
