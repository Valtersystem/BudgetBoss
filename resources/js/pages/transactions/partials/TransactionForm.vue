<script setup lang="ts">
import { defineProps, defineEmits, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Switch } from '@/components/ui/switch';
import InputError from '@/components/InputError.vue';

type SelectOption = { id: number; name: string; };

const props = defineProps<{
    form: {
        description: string;
        amount: number;
        type: 'income' | 'expense';
        date: string;
        paid: boolean;
        account_id: number | null;
        category_id: number | null;
        tags: number[];
        errors: Record<string, string>;
        processing: boolean;
        is_recurring: boolean;
        installments: number;
        frequency: string;
    };
    accounts: Array<SelectOption>;
    categories: Array<SelectOption>;
    tags: Array<SelectOption>;
}>();

const emit = defineEmits(['submit']);
const showDetails = ref(false);
</script>

<template>
    <form @submit.prevent="emit('submit')" class="max-w-4xl mx-auto p-6 space-y-6">
        <div
  :class="['grid', 'grid-cols-1', 'gap-x-8', 'py-6', showDetails ? 'md:grid-cols-2' : '']"
>
            <div class="flex flex-col gap-4">
                <div class="flex items-center space-x-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" v-model="form.type" value="expense" class="h-4 w-4">
                        <span>Expense</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" v-model="form.type" value="income" class="h-4 w-4">
                        <span>Income</span>
                    </label>
                </div>
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Input id="description" v-model="form.description" required />
                    <InputError :message="form.errors.description" />
                </div>
                <div class="grid gap-2">
                    <Label for="amount">Amount</Label>
                    <Input id="amount" type="number" step="0.01" v-model="form.amount" required />
                    <InputError :message="form.errors.amount" />
                </div>
                <div class="grid gap-2">
                    <Label for="date">Date</Label>
                    <Input id="date" type="date" v-model="form.date" required />
                    <InputError :message="form.errors.date" />
                </div>
                <div class="grid gap-2">
                    <Label for="account_id">Account</Label>
                    <Select v-model="form.account_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Select an account" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="account in accounts" :key="account.id" :value="account.id">
                                {{ account.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.account_id" />
                </div>
                <div class="grid gap-2">
                    <Label for="category_id">Category</Label>
                    <Select v-model="form.category_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Select a category" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.category_id" />
                </div>
                <div class="flex items-center space-x-2">
                    <Checkbox id="paid" v-model:checked="form.paid" />
                    <Label for="paid" class="font-normal">
                        {{ form.type === 'income' ? 'Received' : 'Paid' }}
                    </Label>
                </div>
                <div>
                    <Button variant="link" type="button" @click="showDetails = !showDetails" class="p-0 h-auto text-sm">
                        {{ showDetails ? 'Menos detalhes' : 'Mais detalhes' }}
                    </Button>
                </div>
            </div>

            <div v-if="showDetails" class="flex flex-col gap-4 mt-4 md:mt-0">
                <div  class="grid gap-4 animate-in fade-in-0">
                    <div class="flex items-center justify-between">
                        <Label for="is_recurring" class="font-normal">Repetir transação</Label>
                        <Switch id="is_recurring" v-model:checked="form.is_recurring" />
                    </div>
                    <div v-if="form.is_recurring" class="grid grid-cols-2 gap-4 items-end animate-in fade-in-0">
                        <div class="grid gap-2">
                            <Label for="installments">Vezes</Label>
                            <Input id="installments" type="number" v-model="form.installments" />
                            <InputError :message="form.errors.installments" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="frequency">Frequência</Label>
                            <Select v-model="form.frequency">
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="daily">Dias</SelectItem>
                                    <SelectItem value="weekly">Semanas</SelectItem>
                                    <SelectItem value="monthly">Meses</SelectItem>
                                    <SelectItem value="yearly">Anos</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.frequency" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
