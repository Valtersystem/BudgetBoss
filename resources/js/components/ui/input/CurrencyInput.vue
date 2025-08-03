<script setup lang="ts">
import { computed } from 'vue'
import { useVModel } from '@vueuse/core'
import { cn } from '@/lib/utils'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps<{
    modelValue?: number | null
    currency?: string
    error?: boolean
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', payload: number | null): void
    (e: 'update:currency', payload: string): void
}>()

const modelValue = useVModel(props, 'modelValue', emits)
const currency = useVModel(props, 'currency', emits)

const formattedValue = computed({
    get() {
        if (typeof modelValue.value !== 'number') {
            return ''
        }
        return modelValue.value.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
    },
    set(newValue) {
        const cleanedString = newValue.replace(/[^\d,]/g, '').replace(',', '.')
        const parsed = parseFloat(cleanedString)
        modelValue.value = isNaN(parsed) ? null : parsed
    },
})
</script>

<template>
    <div
        :class="cn(
      'flex items-center border-b-2 transition-colors focus-within:border-primary',
      props.error ? 'border-destructive' : 'border-input',
    )"
    >
        <span class="text-3xl font-light text-muted-foreground">R$</span>
        <input
            v-model="formattedValue"
            type="text"
            inputmode="decimal"
            placeholder="0,00"
            :class="cn(
        'h-auto w-full min-w-0 flex-1 border-0 bg-transparent p-0 pl-2 text-3xl',
        'placeholder:text-muted-foreground/50 focus:ring-0 focus-visible:ring-0',
      )"
        >
        <Select v-model="currency" default-value="BRL">
            <SelectTrigger class="w-auto border-0 bg-transparent focus:ring-0">
                <SelectValue placeholder="BRL" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="BRL">
                    BRL
                </SelectItem>
                <SelectItem value="USD">
                    USD
                </SelectItem>
                <SelectItem value="EUR">
                    EUR
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
