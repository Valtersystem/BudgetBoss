<script setup lang="ts">
import { reactive, watch, computed } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import InputError from '@/components/InputError.vue';
import CurrencyInput from './CurrencyInput.vue';
import QuickDateChips from './QuickDateChips.vue';
import ToggleButtonGroup from './ToggleButtonGroup.vue';
import { BookCheck } from 'lucide-vue-next';


type SelectOption = { id: number; name: string };

const props = defineProps<{
    form: {
        description: string;
        amount: number;
        type: 'income' | 'expense';
        date: string;
        paid: boolean | null;
        account_id: number | null;
        category_id: number | null;
        tags: number[];
        errors: Record<string, string>;
        processing: boolean | null;
        is_recurring: boolean | null;
        installments: number | null;
        frequency: string | null;
    };
    accounts: Array<SelectOption>;
    categories: Array<SelectOption>;
    tags: Array<SelectOption>;
    isUpdate?: boolean;
}>();

const accountId = computed({
    get: () => localForm.account_id !== null ? String(localForm.account_id) : '',
    set: (val: string) => {
        localForm.account_id = val ? Number(val) : null;
    },
});

const categoryId = computed({
    get: () => localForm.category_id !== null ? String(localForm.category_id) : '',
    set: (val: string) => {
        localForm.category_id = val ? Number(val) : null;
    },
});


const frequency = computed({
    get: () => localForm.frequency || '',
    set: (val: string) => {
        localForm.frequency = val || null;
    },
});


const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: typeof props.form): void;
}>();

const localForm = reactive({ ...props.form });

watch(localForm, (newVal) => {
    emit('update:form', newVal);
}, { deep: true });
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <div class="grid md:grid-cols-2 gap-x-6 py-6 transition-[grid-template-columns] duration-200 relative">
            <div class="flex flex-col gap-4 px-6">
                <!-- TIPO (Expense / Income) -->
                <div class="flex items-center gap-2">
                    <ToggleButtonGroup v-model="localForm.type" :options="[
                        { label: 'Expense', value: 'expense', color: 'bg-red-500 text-white border-red-500' },
                        { label: 'Income', value: 'income', color: 'bg-green-500 text-white border-green-500' }
                    ]" />
                </div>

                <!-- VALOR -->
                <CurrencyInput v-model="localForm.amount" :type="localForm.type" :error="localForm.errors.amount" />

                <!-- FOI PAGA / RECEBIDA -->
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">
                        Foi {{ localForm.type === 'income' ? 'recebida' : 'paga' }}
                    </span>
                    <Switch v-model:checked="localForm.paid" />
                </div>

                <!-- DATA + CHIPS -->
                <div class="grid gap-2">
                    <QuickDateChips v-model="localForm.date" />
                    <InputError :message="localForm.errors.date" />
                </div>

                <!-- Description -->
                <div class="grid gap-2 mt-2">
                    <div :class="['border-gray-300 loading_input_focus', localForm.type === 'expense' ? 'after:bg-red-500' :
                        localForm.type === 'income' ? 'after:bg-green-500' : 'after:bg-indigo-500']">
                        <BookCheck />
                        <div class="w-full">
                            <input v-model="localForm.description" placeholder="Description" required
                                class="w-full bg-transparent reset_input" />
                            <InputError :message="localForm.errors.description" />
                        </div>
                    </div>
                </div>

                <!-- CATEGORIA -->
                <div class="grid gap-2">
                    <label class="text-sm font-medium">Categoria</label>
                    <div :class="['border-gray-300 loading_input_focus', localForm.type === 'expense' ? 'after:bg-red-500' :
                        localForm.type === 'income' ? 'after:bg-green-500' : 'after:bg-indigo-500']">
                        <Select v-model="categoryId">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecione uma categoria" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in categories" :key="c.id" :value="String(c.id)">
                                    {{ c.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <InputError :message="localForm.errors.category_id" />
                </div>


                <!-- CONTA -->
                <div class="grid gap-2">
                    <label class="text-sm font-medium">Conta</label>
                    <div :class="['border-gray-300 loading_input_focus', localForm.type === 'expense' ? 'after:bg-red-500' :
                        localForm.type === 'income' ? 'after:bg-green-500' : 'after:bg-indigo-500']">
                        <Select v-model="accountId" class="bg-red-100">
                            <SelectTrigger class="bg-transparent border-none w-full">
                                <SelectValue placeholder="Selecione uma conta" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="a in accounts" :key="a.id" :value="String(a.id)">
                                    {{ a.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <InputError :message="localForm.errors.account_id" />
                </div>

            </div>

            <!-- PAINEL LATERAL (EXTENSÃO) -->
            <div class="border-l px-6 py-4">
                <div class="flex flex-col gap-4 h-full">
                    <div class="flex items-center justify-between">
                        <label class="font-normal text-sm">Repetir transação</label>
                        <Switch v-model:checked="localForm.is_recurring" />
                    </div>

                    <div v-if="localForm.is_recurring" class="flex flex-col items-start gap-4 width-full">
                        <div class="flex flex-col justify-between gap-2 w-full">
                            <label for="installments">Vezes</label>
                            <input id="installments" type="number" v-model.number="localForm.installments"
                                class="bg-transparent border rounded px-2 py-1" />
                            <InputError :message="localForm.errors.installments" />
                        </div>
                        <div class="flex flex-col justify-between gap-2 w-full">
                            <label for="frequency">Frequência</label>
                            <Select v-model="frequency">
                                <SelectTrigger class="bg-transparent border-none w-full">
                                    <SelectValue placeholder="Selecione" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="daily">Dias</SelectItem>
                                    <SelectItem value="weekly">Semanas</SelectItem>
                                    <SelectItem value="monthly">Meses</SelectItem>
                                    <SelectItem value="yearly">Anos</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="localForm.errors.frequency" />
                        </div>
                    </div>

                    <!-- Ignorar transação -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Ignorar transação</span>
                        <Switch v-model:checked="(localForm as any).ignored" />
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
