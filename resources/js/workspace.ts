export interface Client {
    id: number
    name: string | null
    nickname: string | null
    reference: string
    mobile: string
}

export interface Policy {
    id: number
    insurer: string
    product: string
    reference: string | null
    filing_reference: string | null
    event_label: string
    due_on: string
    documents: PolicyDocument[]
}

export interface PolicyDocument {
    id: number
    name: string
    status: 'Missing' | 'Received' | 'Submitted'
    updated_at: string
}

export interface Callback {
    id: number
    policy: Pick<Policy, 'id' | 'product'> | null
    due_at: string
    reason: string
}

export interface Page<T> {
    data: T[]
    prev_page_url: string | null
    next_page_url: string | null
}

export function clientName(client: Pick<Client, 'name' | 'nickname' | 'reference'>) {
    return client.nickname || client.name || client.reference
}

export function clientUrl(id: number) {
    return '/workspace/clients/' + id
}

const time = new Intl.DateTimeFormat('en-PH', {
    timeZone: 'Asia/Manila',
    day: 'numeric',
    month: 'short',
    hour: 'numeric',
    minute: '2-digit',
})

export function manilaTime(iso: string) {
    return time.format(new Date(iso))
}

export function calendarDate(date: string) {
    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium', timeZone: 'UTC',
    }).format(new Date(date + 'T00:00:00Z'))
}
