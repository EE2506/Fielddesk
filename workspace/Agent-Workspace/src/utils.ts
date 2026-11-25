export function parseLocalDate(value: string): Date {
  const [year = 2026, month = 1, day = 1] = value.split('-').map(Number)
  return new Date(year, month - 1, day)
}

export function formatDate(value: string, options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short' }): string {
  return parseLocalDate(value).toLocaleDateString('en-PH', options)
}

export function formatLongDate(value: string): string {
  return parseLocalDate(value).toLocaleDateString('en-PH', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

export function formatMoney(value: number): string {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(value)
}

export function maskMobile(value: string): string {
  const digits = value.replace(/\D/g, '')
  const local = digits.startsWith('63') ? digits.slice(2) : digits
  const area = local.slice(0, 3)
  const tail = local.slice(-4)
  return `+63 ${area} ••• ${tail}`
}

export function daysBetween(from: string, to: string): number {
  return Math.round((parseLocalDate(to).getTime() - parseLocalDate(from).getTime()) / 86_400_000)
}

export function friendlyCallback(date: string, time: string, demoDate: string): string {
  const delta = daysBetween(demoDate, date)
  const day = delta === 0 ? 'Today' : delta === 1 ? 'Tomorrow' : parseLocalDate(date).toLocaleDateString('en-PH', { weekday: 'long' })
  const [hour = 0, minute = 0] = time.split(':').map(Number)
  const timeText = new Date(2026, 0, 1, hour, minute).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit' })
  return `${day}, ${formatDate(date)} · ${timeText}`
}
