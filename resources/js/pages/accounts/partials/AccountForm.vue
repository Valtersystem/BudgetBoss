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
type SelectOption = {
    id: number;
    name: string;
};

const props = defineProps<{
    form: {
        name: string;
        initial_balance: number;
        color: string;
        include_in_dashboard: boolean;
        financial_institution_id: number | null;
        account_type_id: number | null;
        errors: Record<string, string>;
        processing: boolean;
    };
    financial_institutions: Array<SelectOption>;
    account_types: Array<SelectOption>;
    isUpdate: boolean;
}>();

const emit = defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <DialogHeader>
            <DialogTitle>{{ isUpdate ? 'Edit Account' : 'Register New Account' }}</DialogTitle>
            <DialogDescription>
                Fill in the details for your account below.
            </DialogDescription>
        </DialogHeader>

        <div class="grid gap-4 py-6">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" v-model="form.name" required />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="financial_institution_id">Financial Institution</Label>
                <Select v-model="form.financial_institution_id">
                    <SelectTrigger>
                        <SelectValue placeholder="Select an institution" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="institution in financial_institutions" :key="institution.id" :value="institution.id">
                            {{ institution.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.financial_institution_id" />
            </div>

            <div class="grid gap-2">
                <Label for="account_type_id">Account Type</Label>
                 <Select v-model="form.account_type_id">
                    <SelectTrigger>
                        <SelectValue placeholder="Select a type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="accountType in account_types" :key="accountType.id" :value="accountType.id">
                            {{ accountType.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.account_type_id" />
            </div>

            <div class="grid gap-2">
                <Label for="initial_balance">Initial Balance</Label>
                <Input id="initial_balance" type="number" step="0.01" v-model="form.initial_balance" required />
                <InputError :message="form.errors.initial_balance" />
            </div>

            <div class="grid gap-2">
                <Label for="color">Color</Label>
                <div class="relative flex items-center">
                    <Input id="color" v-model="form.color" class="w-full pr-10" />
                    <input type="color" v-model="form.color" class="absolute right-2 h-6 w-6 cursor-pointer bg-transparent border-none">
                </div>
                <InputError :message="form.errors.color" />
            </div>

            <div class="flex items-center space-x-2">
                <Checkbox id="include_in_dashboard" v-model:checked="form.include_in_dashboard" />
                <Label for="include_in_dashboard" class="font-normal">
                    Include account balance in dashboard total.
                </Label>
            </div>
            <InputError :message="form.errors.include_in_dashboard" />

        </div>

        <DialogFooter>
            <Button type="submit" :disabled="form.processing">
                {{ isUpdate ? 'Update Account' : 'Save Account' }}
            </Button>
        </DialogFooter>
    </form>
</template>
