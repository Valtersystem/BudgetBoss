<script setup lang="ts">
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    modelValue: number | null
    currency?: string
    locale?: string
    error?: string | null
    type?: 'income' | 'expense'
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void
}>();

const locale = props.locale ?? 'pt-BR';
const currency = props.currency ?? 'BRL';

const raw = ref<string>(
    props.modelValue != null
        ? new Intl.NumberFormat(locale, { style: 'currency', currency }).format(props.modelValue / 100)
        : ''
);

// sempre que vier algo de fora, atualiza o texto
watch(() => props.modelValue, (v) => {
    if (document.activeElement !== inputEl.value) {
        raw.value = v != null
            ? new Intl.NumberFormat(locale, { style: 'currency', currency }).format(v / 100)
            : '';
    }
});

const inputEl = ref<HTMLInputElement | null>(null);

function onlyDigits(str: string) {
    return str.replace(/\D+/g, '');
}

function onInput(e: Event) {
    const target = e.target as HTMLInputElement;
    const digits = onlyDigits(target.value);
    const cents = digits === '' ? 0 : parseInt(digits, 10);
    emit('update:modelValue', isNaN(cents) ? null : cents); // guardando em centavos
    raw.value = new Intl.NumberFormat(locale, { style: 'currency', currency }).format(cents / 100);
}

const borderColorClass = computed(() =>
    props.type === 'expense' ? 'border-red-500 focus-visible:ring-red-500' : 'border-emerald-500 focus-visible:ring-emerald-500'
);
</script>

<template>
    <div class="grid gap-1">
        <div class="relative">
            <input
                ref="inputEl"
                :value="raw"
                @input="onInput"
                class="w-full bg-transparent rounded-md border px-3 py-2 text-2xl font-semibold tracking-tight
               dark:text-white dark:bg-zinc-900/50 focus-visible:outline-none focus-visible:ring-2
               transition-colors"
                :class="borderColorClass"
                inputmode="decimal"
                placeholder="R$ 0,00"
            />
            <span v-if="!modelValue || modelValue === 0" class="absolute -bottom-5 left-0 text-xs text-amber-400">
        Deve ter um valor maior que 0
      </span>
        </div>
        <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
    </div>
</template>
