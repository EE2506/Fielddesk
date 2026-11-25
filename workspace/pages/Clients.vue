<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import ClientForm from '../../resources/js/components/ClientForm.vue'
import Pager from '../../resources/js/components/Pager.vue'
import WorkspaceLayout from '../../resources/js/components/WorkspaceLayout.vue'
import { clientName, clientUrl, type Client, type Page } from '../../resources/js/workspace'

const props = defineProps<{ clients: Page<Client>; query: string }>()
const search = useForm({ q: props.query })
</script>

<template>
  <WorkspaceLayout title="Clients">
    <header class="page-title"><h1>Clients</h1></header>
    <div class="day-columns">
      <section class="panel">
        <h2>Find a client</h2>
        <form class="search-form" @submit.prevent="search.post('/workspace/clients/search')">
          <label>Mobile, name, nickname or client reference
            <input v-model="search.q" type="search" maxlength="120">
          </label>
          <button :disabled="search.processing">Search</button>
        </form>
        <p v-if="search.errors.q" role="alert">{{ search.errors.q }}</p>
        <p v-if="!clients.data.length" class="muted">{{ query ? 'No matching clients.' : 'No clients added yet.' }}</p>
        <ul class="rows">
          <li v-for="client in clients.data" :key="client.id">
            <Link :href="clientUrl(client.id)">{{ clientName(client) }}</Link>
            <p class="muted">{{ client.mobile }} · {{ client.reference }}</p>
          </li>
        </ul>
        <Pager :page="clients" label="Client pages" />
      </section>
      <section class="panel">
        <h2>Add client</h2>
        <ClientForm />
      </section>
    </div>
  </WorkspaceLayout>
</template>
