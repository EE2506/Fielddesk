<script setup lang="ts">
import { SearchX } from 'lucide-vue-next'
defineProps<{ title: string; description: string; actionLabel?: string }>()
const emit = defineEmits<{ action: [] }>()
</script>
<template>
  <div class="empty-state">
    <div class="empty-state-icon"><SearchX :size="19" /></div>
    <strong>{{ title }}</strong>
    <p>{{ description }}</p>
    <button v-if="actionLabel" class="button button-secondary button-small" @click="emit('action')">{{ actionLabel }}</button>
  </div>
</template>
