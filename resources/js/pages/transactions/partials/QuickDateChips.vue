<script setup lang="ts">
import { ref, watch } from 'vue';
import ToggleButtonGroup from './ToggleButtonGroup.vue';

const selected = ref('today');

defineProps<{
  modelValue: string;
}>();
const emit = defineEmits<{
  (e: 'update:modelValue', v: string): void;
}>();

watch(selected, (val) => {
  if (val === 'today') {
    emit('update:modelValue', new Date().toISOString().slice(0, 10));
  } else if (val === 'yesterday') {
    const d = new Date();
    d.setDate(d.getDate() - 1);
    emit('update:modelValue', d.toISOString().slice(0, 10));
  }
});
</script>

<template>
  <div class="flex items-center gap-2">
    <ToggleButtonGroup
      v-model="selected"
      :options="[
        { label: 'Today', value: 'today', color: 'bg-primary text-primary-foreground' },
        { label: 'Yesterday', value: 'yesterday', color: 'bg-primary text-primary-foreground' }
      ]"
    />

    <input
      type="date"
      :value="modelValue"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      class="ml-2 border rounded-4xl px-4 py-2 text-md bg-gray-800"
    />
  </div>
</template>
