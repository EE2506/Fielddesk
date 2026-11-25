import { reactive } from 'vue'

export const DEMO_AGENT_CODE = 'ANA-4821'

export interface ClientPortalProfile {
  fullName: string
  mobile: string
  email: string
  agentCode: string
}

export const clientPortalAgent = {
  name: 'Ana Reyes',
  initials: 'AR',
  role: 'Your insurance agent',
  mobile: '+639175554821',
}

export const clientPortalPolicies = [
  {
    id: 'life',
    kind: 'Life',
    product: 'Family Protect 20',
    policyNumber: 'HS-L-•••-173',
    status: 'Active',
    dueDate: '2026-12-02',
    dueLabel: 'Premium due 2 Dec',
    amount: 2450,
    details: 'Life policy',
  },
  {
    id: 'motor',
    kind: 'Motor',
    product: 'Comprehensive Motor',
    policyNumber: 'HS-M-•••-482',
    status: 'Active',
    dueDate: '2026-12-10',
    dueLabel: 'Renewal due 10 Dec',
    amount: null,
    details: '2022 Honda City',
  },
] as const

export const clientPortalSession = reactive({ profile: null as ClientPortalProfile | null })

export function connectClientProfile(profile: ClientPortalProfile) {
  // Prototype session only: do not persist client signup details to localStorage or a server.
  clientPortalSession.profile = { ...profile }
}

export function startSampleClient() {
  clientPortalSession.profile = {
    fullName: 'Mila Santos',
    mobile: '+639175554821',
    email: 'mila@example.com',
    agentCode: DEMO_AGENT_CODE,
  }
  return clientPortalSession.profile
}

export function ensureSampleClient() {
  return clientPortalSession.profile ?? startSampleClient()
}

export function clearClientProfile() {
  clientPortalSession.profile = null
}
