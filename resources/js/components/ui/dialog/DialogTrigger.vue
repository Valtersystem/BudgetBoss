<script setup lang="ts">
import { DialogTrigger, type DialogTriggerProps } from 'radix-vue'

const props = defineProps<DialogTriggerProps>()
</script>

<template>
  <DialogTrigger
    data-slot="dialog-trigger"
    v-bind="props"
  >
    <slot />
  </DialogTrigger>
</template>
