<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ArrowLeft, Plus, Save, Trash2 } from 'lucide-vue-next'
import { addPolicy, getClient, getPolicy, updatePolicy } from '../stores/workspace'
import type { Policy, PolicyDocument, DocumentStatus } from '../models'
import EmptyState from './EmptyState.vue'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => Boolean(route.params.policyId))
const policyId = computed(() => String(route.params.policyId || ''))
const clientId = computed(() => String(route.params.clientId || ''))
const existing = computed(() => isEdit.value ? getPolicy(policyId.value) : undefined)
const client = computed(() => existing.value ? getClient(existing.value.clientId) : getClient(clientId.value))
const form = reactive({ kind: 'Motor' as 'Motor'|'Life', insurer: '', product: '', policyNumber: '', filingReference: '', vehicle: '', coverageStart: '', coverageEnd: '', renewalDue: '', premiumDue: '', premiumAmount: '' })
const documents = ref<Array<PolicyDocument & { key: string }>>([])
const errors = ref<Record<string,string>>({})
watch(existing, policy => {
  if (policy) {
    Object.assign(form, { kind: policy.kind, insurer: policy.insurer, product: policy.product, policyNumber: policy.policyNumber, filingReference: policy.filingReference, vehicle: policy.vehicle || '', coverageStart: policy.coverageStart, coverageEnd: policy.coverageEnd, renewalDue: policy.renewalDue || '', premiumDue: policy.premiumDue || '', premiumAmount: String(policy.premiumAmount) })
    documents.value = policy.documents.map(doc => ({ ...doc, key: doc.id }))
  } else {
    form.coverageStart = '2026-11-25'
    form.coverageEnd = '2027-11-24'
    form.renewalDue = ''
    form.premiumDue = ''
    documents.value = []
  }
}, { immediate: true })
function addDocument() { documents.value.push({ id: `doc-${Math.random().toString(36).slice(2, 7)}`, key: `key-${Math.random().toString(36).slice(2, 7)}`, name: '', status: 'Missing' }) }
function validate() {
  const next: Record<string,string> = {}
  if (!form.insurer.trim()) next.insurer = 'Enter the insurer.'
  if (!form.product.trim()) next.product = 'Enter the policy or product name.'
  if (!form.coverageStart) next.coverageStart = 'Choose a coverage start date.'
  if (!form.coverageEnd) next.coverageEnd = 'Choose a coverage end date.'
  else if (form.coverageStart && form.coverageEnd < form.coverageStart) next.coverageEnd = 'Coverage end must be on or after the start date.'
  if (form.kind === 'Motor' && !form.renewalDue) next.renewalDue = 'A motor policy needs its renewal date.'
  if (form.kind === 'Life' && !form.premiumDue) next.premiumDue = 'A life policy needs its premium due date.'
  const amount = Number(form.premiumAmount)
  if (!form.premiumAmount || !Number.isFinite(amount) || amount <= 0) next.premiumAmount = 'Enter a premium amount greater than zero.'
  if (documents.value.some(doc => !doc.name.trim())) next.documents = 'Give each document a name or remove its empty row.'
  errors.value = next
  return Object.keys(next).length === 0
}
function submit() {
  if (!validate() || !client.value) return
  const values: Omit<Policy,'id'> = {
    clientId: client.value.id, kind: form.kind, insurer: form.insurer.trim(), product: form.product.trim(),
    policyNumber: form.policyNumber.trim() || 'Unassigned', filingReference: form.filingReference.trim() || 'Private filing ref · not entered',
    vehicle: form.kind === 'Motor' ? form.vehicle.trim() : undefined,
    coverageStart: form.coverageStart, coverageEnd: form.coverageEnd,
    renewalDue: form.kind === 'Motor' ? form.renewalDue : undefined,
    premiumDue: form.kind === 'Life' ? form.premiumDue : undefined,
    premiumAmount: Number(form.premiumAmount),
    documents: documents.value.map(({ key: _key, ...doc }) => ({ ...doc, name: doc.name.trim(), status: doc.status as DocumentStatus })),
  }
  if (existing.value) {
    const saved = updatePolicy(existing.value.id, values)
    if (saved) router.push(`/policies/${saved.id}`)
  } else {
    const saved = addPolicy(values)
    router.push(`/policies/${saved.id}`)
  }
}
</script>

<template>
  <button class="back-link" @click="router.push(client ? `/clients/${client.id}` : '/clients')"><ArrowLeft :size="15" /> Back to {{ client?.nickname || 'client' }}</button>
  <div v-if="client" class="page-head"><div><span class="eyebrow">{{ client.name }} · POLICY RECORD</span><h1>{{ isEdit ? 'Edit policy' : 'Add a policy' }}</h1><p>Policy dates and documents remain separate from conversation notes.</p></div></div>
  <form v-if="client" class="card" novalidate @submit.prevent="submit">
    <section class="form-section"><h2>Policy details</h2><p>Motor renewal dates and life premium dates are stored independently.</p>
      <div class="form-grid">
        <div class="form-field"><label class="field-label" for="policy-kind">Policy type <span class="required-mark">*</span></label><select id="policy-kind" v-model="form.kind" class="select"><option value="Motor">Motor</option><option value="Life">Life</option></select></div>
        <div class="form-field"><label class="field-label" for="insurer">Insurer <span class="required-mark">*</span></label><input id="insurer" v-model="form.insurer" class="input" maxlength="70" placeholder="e.g. HarborSure" /><span v-if="errors.insurer" class="field-error">{{ errors.insurer }}</span></div>
        <div class="form-field"><label class="field-label" for="product">Policy / product <span class="required-mark">*</span></label><input id="product" v-model="form.product" class="input" maxlength="90" placeholder="e.g. Comprehensive Motor" /><span v-if="errors.product" class="field-error">{{ errors.product }}</span></div>
        <div v-if="form.kind === 'Motor'" class="form-field"><label class="field-label" for="vehicle">Vehicle <span style="color:#8495a3;font-weight:400">· optional</span></label><input id="vehicle" v-model="form.vehicle" class="input" maxlength="80" placeholder="Year, make and model" /></div>
        <div class="form-field"><label class="field-label" for="policy-number">Policy number <span style="color:#8495a3;font-weight:400">· optional</span></label><input id="policy-number" v-model="form.policyNumber" class="input" maxlength="50" placeholder="Keep masked if shown" /></div>
        <div class="form-field"><label class="field-label" for="premium-amount">Premium amount (PHP) <span class="required-mark">*</span></label><input id="premium-amount" v-model="form.premiumAmount" class="input" type="number" min="1" step="1" placeholder="0" /><span v-if="errors.premiumAmount" class="field-error">{{ errors.premiumAmount }}</span></div>
      </div>
    </section>
    <section class="form-section"><h2>Coverage and due dates</h2><p>These policy dates are unchanged when a document is marked received.</p>
      <div class="form-grid">
        <div class="form-field"><label class="field-label" for="coverage-start">Coverage start <span class="required-mark">*</span></label><input id="coverage-start" v-model="form.coverageStart" class="input" type="date" /><span v-if="errors.coverageStart" class="field-error">{{ errors.coverageStart }}</span></div>
        <div class="form-field"><label class="field-label" for="coverage-end">Coverage end <span class="required-mark">*</span></label><input id="coverage-end" v-model="form.coverageEnd" class="input" type="date" /><span v-if="errors.coverageEnd" class="field-error">{{ errors.coverageEnd }}</span></div>
        <div v-if="form.kind === 'Motor'" class="form-field"><label class="field-label" for="renewal-date">Motor renewal due <span class="required-mark">*</span></label><input id="renewal-date" v-model="form.renewalDue" class="input" type="date" /><span v-if="errors.renewalDue" class="field-error">{{ errors.renewalDue }}</span></div>
        <div v-if="form.kind === 'Life'" class="form-field"><label class="field-label" for="premium-date">Life premium due <span class="required-mark">*</span></label><input id="premium-date" v-model="form.premiumDue" class="input" type="date" /><span v-if="errors.premiumDue" class="field-error">{{ errors.premiumDue }}</span></div>
        <div class="form-field"><label class="field-label" for="filing-reference">Private insurer filing reference <span style="color:#8495a3;font-weight:400">· optional</span></label><input id="filing-reference" v-model="form.filingReference" class="input" maxlength="100" placeholder="Private reference in existing insurer records" /></div>
      </div>
    </section>
    <section class="form-section"><div class="section-heading"><div><h2>Documents</h2><p>Add a document and its current status.</p></div><button class="button button-secondary button-small" type="button" @click="addDocument"><Plus :size="14" /> Add document</button></div>
      <div v-if="errors.documents" class="field-error" style="margin:0 0 10px">{{ errors.documents }}</div>
      <div v-if="documents.length" style="display:grid;gap:10px">
        <div v-for="document in documents" :key="document.key" class="form-grid" style="grid-template-columns:minmax(0,1fr) 150px 34px;gap:8px;align-items:end">
          <div class="form-field"><label class="field-label">Document name</label><input v-model="document.name" class="input" maxlength="80" placeholder="e.g. Updated OR/CR" /></div>
          <div class="form-field"><label class="field-label">Status</label><select v-model="document.status" class="select"><option>Missing</option><option>Received</option><option>Submitted</option></select></div>
          <button class="icon-button-small" type="button" aria-label="Remove document" @click="documents = documents.filter(item => item.key !== document.key)"><Trash2 :size="15" /></button>
        </div>
      </div>
      <div v-else class="field-hint">No documents added. You can add these later.</div>
    </section>
    <div class="form-footer"><span class="form-footer-note">{{ client.name }} · {{ isEdit ? 'Edit keeps this policy’s due dates on the policy record.' : 'New dates will appear on Today.' }}</span><div class="form-footer-actions"><RouterLink class="button button-secondary" :to="`/clients/${client.id}`">Cancel</RouterLink><button class="button button-primary" type="submit"><Save :size="15" /> {{ isEdit ? 'Save policy' : 'Add policy' }}</button></div></div>
  </form>
  <section v-else class="card card-pad"><EmptyState title="Client not found" description="A policy must belong to a client." action-label="Find a client" @action="router.push('/clients')" /></section>
</template>
