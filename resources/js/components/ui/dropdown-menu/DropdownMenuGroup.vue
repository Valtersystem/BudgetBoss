<script setup lang="ts">
import { DropdownMenuGroup, type DropdownMenuGroupProps } from 'radix-vue'

const props = defineProps<DropdownMenuGroupProps>()
</script>

<template>
  <DropdownMenuGroup
    data-slot="dropdown-menu-group"
    v-bind="props"
  >
    <slot />
  </DropdownMenuGroup>
</template>
