<template>
  <div class="toast-wrap">
    <div v-for="t in items" :key="t.id" class="toast">
      <span style="flex:1">{{ t.message }}</span>
      <button type="button" aria-label="Dismiss" @click="$emit('dismiss', t.id)">✕</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ToastRegion',
  props: { items: { type: Array, default: () => [] } },
  emits: ['dismiss'],
};
</script>
