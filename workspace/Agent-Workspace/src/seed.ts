import type { WorkspaceData } from './models'

export function createDemoData(): WorkspaceData {
  return {
    demoDate: '2026-11-25',
    clients: [
      {
        id: 'mila', name: 'Mila Santos', nickname: 'Mila', reference: 'CL-2048',
        mobile: '+639175554821', notes: 'Prefers a quick call after work. Address her as Ma’am.',
      },
      {
        id: 'benjie', name: 'Benjie dela Cruz', nickname: 'Benjie', reference: 'CL-1186',
        mobile: '+639185553604', notes: 'Usually available before lunch.',
      },
      {
        id: 'lorna', name: 'Lorna Reyes', nickname: 'Nay Lorna', reference: 'CL-3301',
        mobile: '+639175551936', notes: 'Daughter may help coordinate paperwork.',
      },
      {
        id: 'rafi', name: 'Rafi Mendoza', nickname: 'Rafi', reference: 'CL-0914',
        mobile: '+639055557604', notes: '',
      },
    ],
    policies: [
      {
        id: 'mila-motor', clientId: 'mila', kind: 'Motor', insurer: 'HarborSure',
        product: 'Comprehensive Motor', policyNumber: 'HS-M-•••-482', filingReference: 'Private filing ref · HS / 2024 / M4821',
        vehicle: '2022 Honda City', coverageStart: '2025-12-10', coverageEnd: '2026-12-09',
        renewalDue: '2026-12-10', premiumAmount: 18600,
        documents: [
          { id: 'motor-orcr', name: 'Updated OR/CR', status: 'Missing' },
          { id: 'motor-id', name: 'Valid ID', status: 'Received' },
          { id: 'motor-quote', name: 'Renewal quotation', status: 'Submitted' },
        ],
      },
      {
        id: 'mila-life', clientId: 'mila', kind: 'Life', insurer: 'HarborSure Life',
        product: 'Family Protect 20', policyNumber: 'HS-L-•••-173', filingReference: 'Private filing ref · HSL / 2019 / L1739',
        coverageStart: '2019-12-02', coverageEnd: '2039-12-01', premiumDue: '2026-12-02',
        premiumAmount: 2450,
        documents: [
          { id: 'life-form', name: 'Annual declaration', status: 'Received' },
          { id: 'life-proof', name: 'Premium payment proof', status: 'Missing' },
          { id: 'life-receipt', name: 'Official receipt', status: 'Submitted' },
        ],
      },
      {
        id: 'benjie-life', clientId: 'benjie', kind: 'Life', insurer: 'Northfield Life',
        product: 'Secure Years', policyNumber: 'NF-L-•••-609', filingReference: 'Private filing ref · NFL / 2021 / L6093',
        coverageStart: '2021-11-30', coverageEnd: '2041-11-29', premiumDue: '2026-11-29',
        premiumAmount: 3200,
        documents: [
          { id: 'benjie-id', name: 'Valid ID', status: 'Received' },
          { id: 'benjie-health', name: 'Health declaration', status: 'Missing' },
        ],
      },
      {
        id: 'lorna-motor', clientId: 'lorna', kind: 'Motor', insurer: 'HarborSure',
        product: 'Third-Party Liability', policyNumber: 'HS-M-•••-882', filingReference: 'Private filing ref · HS / 2023 / M8820',
        vehicle: '2019 Toyota Vios', coverageStart: '2025-11-20', coverageEnd: '2026-11-19',
        renewalDue: '2026-11-20', premiumAmount: 7600,
        documents: [{ id: 'lorna-orcr', name: 'Updated OR/CR', status: 'Received' }],
      },
      {
        id: 'rafi-life', clientId: 'rafi', kind: 'Life', insurer: 'Northfield Life',
        product: 'Future Builder', policyNumber: 'NF-L-•••-230', filingReference: 'Private filing ref · NFL / 2020 / L2304',
        coverageStart: '2020-11-25', coverageEnd: '2040-11-24', premiumDue: '2026-12-18',
        premiumAmount: 2800,
        documents: [{ id: 'rafi-proof', name: 'Payment proof', status: 'Submitted' }],
      },
    ],
    callbacks: [
      { id: 'cb-lorna', clientId: 'lorna', policyId: 'lorna-motor', date: '2026-11-24', time: '15:00', reason: 'Confirm the insurer received her renewal payment', status: 'Scheduled' },
      { id: 'cb-benjie', clientId: 'benjie', policyId: 'benjie-life', date: '2026-11-25', time: '11:30', reason: 'Ask about the missing health declaration', status: 'Scheduled' },
    ],
    contacts: [
      { id: 'contact-mila-1', clientId: 'mila', policyId: 'mila-motor', happenedOn: '2026-11-20', happenedAt: '16:12', outcome: 'Reached', note: 'Asked for an updated comprehensive renewal quote. I said I would check the insurer options before we speak again.', },
      { id: 'contact-mila-2', clientId: 'mila', policyId: 'mila-life', happenedOn: '2026-10-30', happenedAt: '10:05', outcome: 'Reached', note: 'Premium reminder sent. Mila asked me to call again closer to the due date.', },
      { id: 'contact-benjie-1', clientId: 'benjie', policyId: 'benjie-life', happenedOn: '2026-11-21', happenedAt: '09:40', outcome: 'Reached', note: 'He will send the signed health declaration after checking his records.', },
      { id: 'contact-lorna-1', clientId: 'lorna', policyId: 'lorna-motor', happenedOn: '2026-11-19', happenedAt: '14:20', outcome: 'Left message', note: 'Sent a reminder about the renewal and asked her to confirm payment status.', },
    ],
    reminders: { enabled: true, callbackTime: '08:30', policyDaysAhead: 7, dailySummary: true },
  }
}
