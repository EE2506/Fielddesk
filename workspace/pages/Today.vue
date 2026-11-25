<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import WorkspaceLayout from '../../resources/js/components/WorkspaceLayout.vue'
import Pager from '../../resources/js/components/Pager.vue'
import { calendarDate, clientName, clientUrl, type Callback, type Client, type Page, type Policy } from '../../resources/js/workspace'

const props = defineProps<{
  date: string
  today: string
  noticeThrough: string
  followUps: Page<Callback & { client: Client; group: string }>
  deadlines: Page<Pick<Policy, 'id' | 'product' | 'event_label' | 'due_on'> & { client: Client }>
}>()

const groups = computed(() => ['Overdue', 'Today', 'Upcoming callbacks'].map((name) => ({
  name, items: props.followUps.data.filter((row) => row.group === name),
})).filter((group) => group.items.length))
</script>

<template>
  <WorkspaceLayout title="Today">
    <header class="page-title"><h1>Today</h1><p>{{ date }} · Manila</p></header>
    <div class="day-columns">
      <section class="panel">
        <h2>Follow-ups</h2>
        <p v-if="!followUps.data.length" class="muted">No callbacks to show.</p>
        <section v-for="group in groups" :key="group.name" class="task-group">
          <h3 :class="{ overdue: group.name === 'Overdue' }">{{ group.name }}</h3>
          <ul class="rows">
            <li v-for="item in group.items" :key="item.id">
              <Link :href="clientUrl(item.client.id)">{{ clientName(item.client) }}</Link>
              <p>{{ item.reason }}<template v-if="item.policy"> · {{ item.policy.product }}</template></p>
              <p class="muted">{{ item.due_at }}</p>
              <div class="actions">
                <a :href="'tel:' + item.client.mobile">Call</a>
                <a :href="'sms:' + item.client.mobile">Text</a>
                <Link :href="clientUrl(item.client.id) + '?callback=' + item.id + '#log-contact'">Log contact</Link>
              </div>
            </li>
          </ul>
        </section>
        <Pager :page="followUps" label="Callback pages" />
      </section>
      <section class="panel">
        <h2>Policy deadlines</h2>
        <p v-if="!deadlines.data.length" class="muted">Add a client policy to see its deadline.</p>
        <ul class="rows">
          <li v-for="policy in deadlines.data" :key="policy.id">
            <Link :href="clientUrl(policy.client.id) + '#policy-' + policy.id">{{ policy.product }}</Link>
            <p>{{ clientName(policy.client) }} · {{ policy.event_label }}</p>
            <p class="muted">{{ calendarDate(policy.due_on) }} <span v-if="policy.due_on < today" class="overdue">· Past due</span><span v-else-if="policy.due_on <= noticeThrough" class="due-soon">· Due soon</span></p>
          </li>
        </ul>
        <Pager :page="deadlines" label="Policy deadline pages" />
      </section>
    </div>
  </WorkspaceLayout>
</template>
