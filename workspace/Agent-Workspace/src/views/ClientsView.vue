<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ArrowUpRight, Plus, Search, Users } from 'lucide-vue-next'
import { searchClients, getPoliciesForClient } from '../stores/workspace'
import { maskMobile } from '../utils'
import EmptyState from './EmptyState.vue'

const query = ref('')
const results = computed(() => searchClients(query.value))
function initials(name: string) { return name.split(' ').filter(Boolean).slice(0, 2).map(part => part[0]).join('').toUpperCase() }
</script>

<template>
  <div class="page-head">
    <div><span class="eyebrow">YOUR PEOPLE · {{ results.length }} RECORDS</span><h1>Clients</h1><p>Find the person, the policy, and what you agreed on last.</p></div>
    <div class="head-actions"><RouterLink class="button button-primary" to="/clients/new"><Plus :size="16" /> Add client</RouterLink></div>
  </div>

  <div class="client-toolbar">
    <div class="search-box" style="flex:1">
      <Search :size="17" />
      <input v-model="query" class="input" type="search" autocomplete="off" aria-label="Search clients" placeholder="Search mobile number, nickname, or client ref" />
    </div>
    <span class="client-count">{{ results.length }} {{ results.length === 1 ? 'client' : 'clients' }}</span>
  </div>

  <div v-if="results.length" class="client-list">
    <RouterLink v-for="client in results" :key="client.id" class="card client-card" :to="`/clients/${client.id}`">
      <div class="client-avatar">{{ initials(client.name) }}</div>
      <div class="client-card-main">
        <div class="client-card-title"><strong>{{ client.name }}</strong><span v-if="client.nickname && client.nickname !== client.name" class="pill pill-neutral">“{{ client.nickname }}”</span></div>
        <div class="client-card-sub">{{ maskMobile(client.mobile) }} <span aria-hidden="true">·</span> {{ client.reference }}</div>
      </div>
      <div class="client-card-meta"><span><Users :size="13" style="vertical-align:-2px;margin-right:4px" />{{ getPoliciesForClient(client.id).length }} policies</span><span>Open profile <ArrowUpRight :size="13" style="vertical-align:-2px" /></span></div>
      <ArrowUpRight class="client-arrow" :size="17" />
    </RouterLink>
  </div>
  <div v-else class="card card-pad">
    <EmptyState title="No matching clients" :description="query ? `No client matched “${query}”. Try a mobile number, nickname, or reference like CL-2048.` : 'No client records yet. Add a client to keep their number and follow-up details together.'" :action-label="query ? 'Clear search' : 'Add client'" @action="query ? (query = '') : $router.push('/clients/new')" />
  </div>
  <p class="field-hint" style="margin-top:15px"><strong>Privacy note:</strong> all numbers in this prototype are fictional and shown masked. Search accepts full or partial mobile digits.</p>
</template>
