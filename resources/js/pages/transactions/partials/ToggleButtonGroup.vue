<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';

type Option = { label: string; value: string; color?: string };

defineProps<{
  modelValue: string;
  options: Option[];
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', v: string): void;
}>();

function selectOption(val: string) {
  emit('update:modelValue', val);
}
</script>

<template>
  <div class="flex items-center gap-2">
    <button
      v-for="opt in options"
      :key="opt.value"
      type="button"
      @click="selectOption(opt.value)"
      :class="[
        'px-4 py-2 rounded-full text-sm border transition-colors',
        modelValue === opt.value
          ? (opt.color ?? 'bg-primary text-primary-foreground border-primary')
          : 'bg-zinc-700/40 text-white border-transparent hover:bg-zinc-600/40'
      ]"
    >
      {{ opt.label }}
    </button>
  </div>
</template>
