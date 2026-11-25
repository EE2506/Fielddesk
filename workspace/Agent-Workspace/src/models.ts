export type DocumentStatus = 'Missing' | 'Received' | 'Submitted'
export type ContactOutcome = 'Reached' | 'No answer' | 'Left message' | 'Text sent'

export interface Client {
  id: string
  name: string
  nickname: string
  reference: string
  mobile: string
  notes: string
}

export interface PolicyDocument {
  id: string
  name: string
  status: DocumentStatus
}

export interface Policy {
  id: string
  clientId: string
  kind: 'Motor' | 'Life'
  insurer: string
  product: string
  policyNumber: string
  filingReference: string
  vehicle?: string
  coverageStart: string
  coverageEnd: string
  renewalDue?: string
  premiumDue?: string
  premiumAmount: number
  documents: PolicyDocument[]
}

export interface Callback {
  id: string
  clientId: string
  policyId: string
  date: string
  time: string
  reason: string
  status: 'Scheduled' | 'Completed'
}

export interface Contact {
  id: string
  clientId: string
  policyId: string
  happenedOn: string
  happenedAt: string
  outcome: ContactOutcome
  note: string
  callbackId?: string
}

export interface ReminderSettings {
  enabled: boolean
  callbackTime: string
  policyDaysAhead: number
  dailySummary: boolean
}

export interface WorkspaceData {
  demoDate: string
  clients: Client[]
  policies: Policy[]
  callbacks: Callback[]
  contacts: Contact[]
  reminders: ReminderSettings
}

export interface ContactDraft {
  clientId: string
  policyId: string
  outcome: ContactOutcome | ''
  note: string
  scheduleCallback: boolean
  callbackDate: string
  callbackTime: string
  callbackReason: string
}
