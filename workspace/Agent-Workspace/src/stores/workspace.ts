import { computed, reactive, ref } from 'vue'
import type { Callback, Client, Contact, ContactDraft, Policy, ReminderSettings, WorkspaceData } from '../models'
import { createDemoData } from '../seed'

const STORAGE_KEY = 'insurance-agent-workspace-v1'
function loadData(): WorkspaceData {
  if (typeof localStorage === 'undefined') return createDemoData()
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved) {
      const parsed = JSON.parse(saved) as WorkspaceData
      if (parsed.clients?.length && parsed.policies?.length && parsed.demoDate) return parsed
    }
  } catch { /* Start with the safe, fictional seed after a damaged browser save. */ }
  return createDemoData()
}

export const state = reactive<WorkspaceData>(loadData())
export const toastMessage = ref('')
export const simulation = ref<{ action: 'call' | 'text'; clientName: string; mobile: string } | null>(null)
const failNextSave = ref(false)
let toastTimer: ReturnType<typeof setTimeout> | undefined

function persist() {
  if (typeof localStorage !== 'undefined') localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
}
function makeId(prefix: string) { return `${prefix}-${Math.random().toString(36).slice(2, 8)}` }

export function notify(message: string) {
  toastMessage.value = message
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toastMessage.value = '' }, 3200)
}

export function useWorkspace() {
  const clients = computed(() => state.clients)
  const policies = computed(() => state.policies)
  const callbacks = computed(() => state.callbacks)
  const contacts = computed(() => state.contacts)
  return { state, clients, policies, callbacks, contacts }
}

export function getClient(clientId: string): Client | undefined { return state.clients.find(client => client.id === clientId) }
export function getPolicy(policyId: string): Policy | undefined { return state.policies.find(policy => policy.id === policyId) }
export function getPoliciesForClient(clientId: string): Policy[] { return state.policies.filter(policy => policy.clientId === clientId) }
export function getClientForPolicy(policyId: string): Client | undefined {
  const policy = getPolicy(policyId)
  return policy ? getClient(policy.clientId) : undefined
}
export function getCallback(callbackId?: string): Callback | undefined {
  return callbackId ? state.callbacks.find(callback => callback.id === callbackId) : undefined
}
export function getContact(contactId: string): Contact | undefined { return state.contacts.find(contact => contact.id === contactId) }
export function getContactsForClient(clientId: string): Contact[] {
  return state.contacts.filter(contact => contact.clientId === clientId).sort((a, b) => `${b.happenedOn}${b.happenedAt}`.localeCompare(`${a.happenedOn}${a.happenedAt}`))
}
export function getNextCallback(clientId: string): Callback | undefined {
  return state.callbacks.filter(callback => callback.clientId === clientId && callback.status === 'Scheduled')
    .sort((a, b) => `${a.date}${a.time}`.localeCompare(`${b.date}${b.time}`))[0]
}
export function searchClients(query: string): Client[] {
  const trimmed = query.trim().toLowerCase()
  if (!trimmed) return state.clients
  const digits = trimmed.replace(/\D/g, '')
  return state.clients.filter(client => {
    const searchable = `${client.name} ${client.nickname} ${client.reference}`.toLowerCase()
    const phoneDigits = client.mobile.replace(/\D/g, '')
    const normalizedDigits = digits.startsWith('0') ? `63${digits.slice(1)}` : digits
    return searchable.includes(trimmed) || (digits.length > 0 && (phoneDigits.includes(digits) || phoneDigits.includes(normalizedDigits)))
  })
}
export function showSimulatedAction(action: 'call' | 'text', client: Client) {
  simulation.value = { action, clientName: client.name, mobile: client.mobile }
}
export function closeSimulation() { simulation.value = null }

export function addClient(values: Omit<Client, 'id' | 'reference'> & { reference?: string }): Client {
  const client: Client = { ...values, id: makeId('client'), reference: values.reference?.trim() || `CL-${Math.floor(1000 + Math.random() * 8999)}` }
  state.clients.unshift(client)
  persist()
  return client
}
export function updateClient(clientId: string, values: Partial<Omit<Client, 'id' | 'reference'>>): Client | undefined {
  const client = getClient(clientId)
  if (!client) return
  Object.assign(client, values)
  persist()
  return client
}

export function addPolicy(values: Omit<Policy, 'id'>): Policy {
  const policy = { ...values, id: makeId('policy') }
  state.policies.unshift(policy)
  persist()
  return policy
}
export function updatePolicy(policyId: string, values: Partial<Omit<Policy, 'id' | 'clientId'>>): Policy | undefined {
  const policy = getPolicy(policyId)
  if (!policy) return
  Object.assign(policy, values)
  persist()
  return policy
}
export function markDocumentReceived(policyId: string, documentId: string) {
  const policy = getPolicy(policyId)
  const document = policy?.documents.find(item => item.id === documentId)
  if (!document) return false
  document.status = 'Received'
  persist()
  notify(`${document.name} marked as received`)
  return true
}

export function saveContact(draft: ContactDraft, happenedOn: string, happenedAt: string, editingId?: string): { ok: boolean; contact?: Contact } {
  if (failNextSave.value) {
    failNextSave.value = false
    return { ok: false }
  }
  const existing = editingId ? state.contacts.find(contact => contact.id === editingId) : undefined
  const oldCallbackId = existing?.callbackId
  if (oldCallbackId) state.callbacks = state.callbacks.filter(callback => callback.id !== oldCallbackId)
  const contact: Contact = {
    id: existing?.id ?? makeId('contact'), clientId: draft.clientId, policyId: draft.policyId,
    happenedOn, happenedAt, outcome: draft.outcome as Contact['outcome'], note: draft.note.trim(), callbackId: undefined,
  }
  if (draft.scheduleCallback) {
    const callback: Callback = {
      id: makeId('callback'), clientId: draft.clientId, policyId: draft.policyId,
      date: draft.callbackDate, time: draft.callbackTime, reason: draft.callbackReason.trim() || 'Follow up on our conversation', status: 'Scheduled',
    }
    state.callbacks.push(callback)
    contact.callbackId = callback.id
  }
  if (existing) Object.assign(existing, contact)
  else state.contacts.unshift(contact)
  persist()
  return { ok: true, contact }
}
export function updateContact(contactId: string, draft: ContactDraft, happenedOn: string, happenedAt: string) {
  return saveContact(draft, happenedOn, happenedAt, contactId)
}
export function setNextSaveToFail() {
  failNextSave.value = true
  notify('Demo save error armed. Your note stays in the form when you save.')
}
export function completeCallback(callbackId: string) {
  const callback = state.callbacks.find(item => item.id === callbackId)
  if (!callback) return
  callback.status = 'Completed'
  persist()
  notify('Callback marked complete')
}
export function updateReminders(values: ReminderSettings) {
  state.reminders = { ...values }
  persist()
  notify('Reminder settings saved')
}
export function advanceToFriday() {
  state.demoDate = '2026-11-27'
  persist()
  notify('Demo advanced to Friday, 27 November')
}
export function returnToWednesday() {
  state.demoDate = '2026-11-25'
  persist()
  notify('Demo returned to Wednesday, 25 November')
}
export function resetDemo() {
  Object.assign(state, createDemoData())
  if (typeof localStorage !== 'undefined') localStorage.removeItem(STORAGE_KEY)
  persist()
  notify('Fictional demo data reset')
}
