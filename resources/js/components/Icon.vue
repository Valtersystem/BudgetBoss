<script setup lang="ts">
import { defineAsyncComponent, computed } from 'vue';
import type { FunctionalComponent } from 'vue';

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
});

/**
 * @param {string} str - A string em kebab-case.
 * @returns {string} A string convertida para PascalCase.
 */
const toPascalCase = (str: string): string => {
  return str
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join('');
};

const iconComponent = computed<FunctionalComponent | null>(() => {
    if (!props.name) return null;

    const pascalName = toPascalCase(props.name);

    return defineAsyncComponent(() =>
        import('lucide-vue-next').then(icons => {
            const component = (icons as any)[pascalName];
            return component || (icons as any)['HelpCircle'];
        })
    );
});
</script>

<template>
  <component :is="iconComponent" v-if="iconComponent" />
</template>
