<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ref, computed } from 'vue'

interface CheckboxProps {
  modelValue?: boolean
  class?: string
  disabled?: boolean
}

const props = defineProps<CheckboxProps>()
const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const checked = computed({
  get: () => props.modelValue ?? false,
  set: (val) => emit('update:modelValue', val),
})
</script>

<template>
  <label
    :class="
      cn(
        'flex items-center gap-2 text-sm font-medium select-none cursor-pointer',
        props.disabled ? 'opacity-50 cursor-not-allowed' : '',
        props.class
      )
    "
  >
    <input
      type="checkbox"
      v-model="checked"
      :disabled="props.disabled"
      class="peer hidden"
    />
    <span
      class="w-4 h-4 border rounded flex items-center justify-center
        peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white"
    >
      <svg
        v-if="checked"
        class="w-3 h-3"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
    </span>
    <slot />
  </label>
</template>
