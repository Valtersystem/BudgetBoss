<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import AccountForm from './partials/AccountForm.vue';
import { type BreadcrumbItem } from '@/types';
import Heading from '@/components/Heading.vue';

// Tipos para os dados do controller
type SelectOption = { id: number; name: string; };
type Account = {
    id: number;
    name: string;
    initial_balance: number;
    color: string;
    include_in_dashboard: boolean;
    financial_institution: SelectOption | null;
    account_type: SelectOption | null;
};

const props = defineProps<{
    accounts: Array<Account>;
    financial_institutions: Array<SelectOption>;
    account_types: Array<SelectOption>;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Accounts', href: '/accounts' },
];

const isModalOpen = ref(false);
const isUpdate = ref(false);

// Atualizar o formulário para usar os IDs
const form = useForm({
    id: null as number | null,
    name: '',
    initial_balance: 0,
    color: '#4A90E2',
    include_in_dashboard: true,
    financial_institution_id: null as number | null,
    account_type_id: null as number | null,
});

const openCreateModal = () => {
    isUpdate.value = false;
    form.reset();
    isModalOpen.value = true;
};

// Atualizar a função para preencher os IDs ao editar
const openUpdateModal = (account: Account) => {
    isUpdate.value = true;
    form.id = account.id;
    form.name = account.name;
    form.initial_balance = account.initial_balance;
    form.color = account.color;
    form.include_in_dashboard = account.include_in_dashboard;
    form.financial_institution_id = account.financial_institution?.id ?? null;
    form.account_type_id = account.account_type?.id ?? null;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    const options = {
        onSuccess: () => closeModal(),
    };
    if (isUpdate.value) {
        form.put(route('accounts.update', form.id), options);
    } else {
        form.post(route('accounts.store'), options);
    }
};

const destroy = (id: number) => {
    if (confirm('Are you sure you want to delete this account?')) {
        router.delete(route('accounts.destroy', id));
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Accounts" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading>Accounts</Heading>
                <Button @click="openCreateModal">New Account</Button>
            </div>

            <div class="border rounded-md">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr class="text-left align-middle">
                            <th class="p-4 font-medium">Name</th>
                            <th class="p-4 font-medium">Type</th>
                            <th class="p-4 font-medium">Balance</th>
                            <th class="p-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="accounts.length === 0">
                            <td colspan="4" class="p-4 text-center text-muted-foreground">
                                No accounts found.
                            </td>
                        </tr>
                        <tr v-for="account in accounts" :key="account.id" class="border-b last:border-b-0">
                            <td class="p-4 flex items-center gap-2">
                                <div class="h-4 w-4 rounded-full" :style="{ backgroundColor: account.color }"></div>
                                <span>{{ account.name }}</span>
                                <span v-if="account.financial_institution" class="text-xs text-muted-foreground">({{ account.financial_institution.name }})</span>
                            </td>
                            <td class="p-4">{{ account.account_type?.name ?? 'N/A' }}</td>
                            <td class="p-4">{{ new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(account.initial_balance) }}</td>
                            <td class="p-4">
                                <div class="flex items-center justify-end space-x-2">
                                    <Button @click="openUpdateModal(account)" variant="ghost" size="icon" class="h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </Button>
                                     <Button @click="destroy(account.id)" variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="closeModal">
            <DialogContent>
                <AccountForm
                    :form="form"
                    :is-update="isUpdate"
                    :financial_institutions="financial_institutions"
                    :account_types="account_types"
                    @submit="submit"
                />
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
