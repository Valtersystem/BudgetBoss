<script setup lang="ts">
import { DropdownMenuRoot, type DropdownMenuRootEmits, type DropdownMenuRootProps, useForwardPropsEmits } from 'radix-vue'

const props = defineProps<DropdownMenuRootProps>()
const emits = defineEmits<DropdownMenuRootEmits>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <DropdownMenuRoot
    data-slot="dropdown-menu"
    v-bind="forwarded"
  >
    <slot />
  </DropdownMenuRoot>
</template>
