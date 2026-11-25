<script setup lang="ts">
defineProps<{ errors: Record<string, string> }>()
</script>

<template>
  <ul v-if="Object.keys(errors).length" class="form-errors" role="alert">
    <li v-for="(error, field) in errors" :key="field">{{ error }}</li>
  </ul>
</template>
