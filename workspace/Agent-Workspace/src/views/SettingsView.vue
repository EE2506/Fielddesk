<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { Bell, CalendarClock, Check, Clock3, ShieldCheck } from 'lucide-vue-next'
import { state, updateReminders } from '../stores/workspace'

const form = reactive({ ...state.reminders })
const errors = ref<Record<string,string>>({})
function toggle(key: 'enabled'|'dailySummary') { form[key] = !form[key] }
function save() {
  const next: Record<string,string> = {}
  if (!form.callbackTime) next.callbackTime = 'Choose a time for your reminder.'
  if (!Number.isInteger(Number(form.policyDaysAhead)) || form.policyDaysAhead < 1 || form.policyDaysAhead > 30) next.policyDaysAhead = 'Choose a number from 1 to 30 days.'
  errors.value = next
  if (Object.keys(next).length === 0) updateReminders({ ...form })
}
</script>

<template>
  <div class="page-head"><div><span class="eyebrow">AGENT PREFERENCES</span><h1>Reminder settings</h1><p>Set when your workspace should surface a promise or a policy deadline.</p></div></div>
  <div class="settings-grid">
    <section class="card">
      <div class="form-section"><h2>Your reminders</h2><p>These are private agent reminders. The prototype does not send SMS, email, or push notifications.</p>
        <div class="setting-row"><div class="setting-copy"><strong>Callback reminders</strong><p>Keep scheduled calls visible in Today and on the selected demo date.</p></div><button :class="['toggle', form.enabled ? 'on' : '']" role="switch" :aria-checked="form.enabled" aria-label="Callback reminders" @click="toggle('enabled')"><span></span></button></div>
        <div class="setting-row"><div class="setting-copy"><strong>Policy deadline lead time</strong><p>Show deadlines this many days before they are due.</p></div><div style="display:flex;align-items:center;gap:7px"><input v-model.number="form.policyDaysAhead" class="input" type="number" min="1" max="30" aria-label="Policy deadline lead time in days" style="width:74px;min-height:37px" /><span style="font-size:10px;color:#74899b">days</span></div></div>
        <div v-if="errors.policyDaysAhead" class="field-error" style="text-align:right">{{ errors.policyDaysAhead }}</div>
        <div class="setting-row"><div class="setting-copy"><strong>Daily callback summary</strong><p>Include calls due today in the workspace summary.</p></div><button :class="['toggle', form.dailySummary ? 'on' : '']" role="switch" :aria-checked="form.dailySummary" aria-label="Daily callback summary" @click="toggle('dailySummary')"><span></span></button></div>
        <div class="setting-row"><div class="setting-copy"><strong>Reminder time</strong><p>When to review the day’s call list.</p></div><input v-model="form.callbackTime" class="input" type="time" aria-label="Reminder review time" style="width:120px;min-height:37px" /></div>
        <div v-if="errors.callbackTime" class="field-error" style="text-align:right">{{ errors.callbackTime }}</div>
        <div style="display:flex;justify-content:flex-end;margin-top:18px"><button class="button button-primary" @click="save"><Check :size="15" /> Save reminders</button></div>
      </div>
    </section>
    <aside class="detail-aside">
      <section class="card reminder-aside"><span class="section-icon"><CalendarClock :size="16" /></span><h2>What moves to Today?</h2><p>Callback dates remain attached to the conversation. When you advance the demo to Friday, the callback for 27 Nov becomes a Friday call.</p><div class="line"></div><span class="eyebrow">POLICY DATES STAY PUT</span><p style="margin-top:7px">Mila’s motor renewal remains due 10 Dec. Her separate life premium remains due 2 Dec—even after a contact is saved or a document status changes.</p></section>
      <section class="card reminder-aside"><span class="section-icon"><Clock3 :size="16" /></span><h2>Demo date</h2><p>The current prototype date is <strong>{{ state.demoDate === '2026-11-25' ? 'Wednesday, 25 Nov 2026' : 'Friday, 27 Nov 2026' }}</strong> in Manila. You can advance it on Today.</p><RouterLink to="/" class="text-link" style="padding:8px 0 0;display:inline-flex">Go to Today</RouterLink></section>
      <p class="demo-about"><ShieldCheck :size="12" style="vertical-align:-2px;margin-right:4px" />Fictional records only. Settings are stored locally in this browser.</p>
    </aside>
  </div>
</template>
