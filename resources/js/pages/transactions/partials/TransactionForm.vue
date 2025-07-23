<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';

// Tipos para os dados que vêm do controller
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
    };
    accounts: Array<SelectOption>;
    categories: Array<SelectOption>;
    tags: Array<SelectOption>;
    isUpdate: boolean;
}>();

const emit = defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <DialogHeader>
            <DialogTitle>{{ isUpdate ? 'Edit Transaction' : 'New Transaction' }}</DialogTitle>
            <DialogDescription>
                Register your income or expense.
            </DialogDescription>
        </DialogHeader>

        <div class="grid gap-4 py-6">
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
                    <SelectTrigger><SelectValue placeholder="Select an account" /></SelectTrigger>
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
                    <SelectTrigger><SelectValue placeholder="Select a category" /></SelectTrigger>
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
        </div>

        <DialogFooter>
            <Button type="submit" :disabled="form.processing">
                {{ isUpdate ? 'Update Transaction' : 'Save Transaction' }}
            </Button>
        </DialogFooter>
    </form>
</template>
