<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Switch } from '@/components/ui/switch';
import AccountForm from './partials/AccountForm.vue';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';
import { HandCoins } from 'lucide-vue-next';

// Tipos
type SelectOption = { id: number; name: string; };
type Account = {
    id: number;
    name: string;
    initial_balance: number;
    color: string;
    include_in_dashboard: boolean;
    financial_institution: SelectOption | null;
    account_type: SelectOption | null;
    current_balance: number;
    predicted_balance: number;
};

const props = defineProps<{
    accounts: Array<Account>;
    financial_institutions: Array<SelectOption>;
    account_types: Array<SelectOption>;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Accounts', href: '/accounts' },
];

const totalCurrentBalance = computed(() => {
    return props.accounts
        .filter(account => account.include_in_dashboard)
        .reduce((sum, account) => sum + account.current_balance, 0);
});

const totalPredictedBalance = computed(() => {
    return props.accounts
        .filter(account => account.include_in_dashboard)
        .reduce((sum, account) => sum + account.predicted_balance, 0);
});

const isModalOpen = ref(false);
const isUpdate = ref(false);
const form = useForm({
    id: null as number | null,
    name: '',
    initial_balance: 0,
    color: '#4A90E2',
    include_in_dashboard: true as boolean,
    financial_institution_id: null as number | null,
    account_type_id: null as number | null,
});

const openCreateModal = () => {
    isUpdate.value = false;
    form.reset();
    isModalOpen.value = true;
};

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

// --- FUNÇÕES EM FALTA RESTAURADAS ---
const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    const options = { onSuccess: () => closeModal() };
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
};

const toggleDashboardInclusion = (account: Account) => {
    router.patch(route('accounts.toggle', account.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <div class="p-4 md:p-0">

            <Head title="Accounts" />

            <div class="space-y-6">
                <div class="flex items-center justify-between md:px-4 mt-6">
                    <Heading title="Accounts" />
                    <Button @click="openCreateModal">New Account</Button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 items-start">
                    <div v-if="accounts.length > 0" class="grid grid-cols-2 lg:grid-cols-2 gap-4 mb-6 md:mb-0 md:px-4">
                        <Card v-for="account in accounts" :key="account.id">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 rounded-full" :style="{ backgroundColor: account.color }"></div>
                                    <h3 class="font-semibold">{{ account.name }}</h3>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8">
                                            <Icon name="MoreVertical" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuItem @click="openUpdateModal(account)">Edit</DropdownMenuItem>
                                        <DropdownMenuItem @click="destroy(account.id)" class="text-destructive">Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </CardHeader>
                            <CardContent>
                                <p class="text-xs text-muted-foreground">{{ account.account_type?.name ?? 'N/A' }}</p>
                                <p class="text-2xl font-bold">
                                    {{ new Intl.NumberFormat('pt-PT', {
                                        style: 'currency', currency: 'EUR'
                                    }).format(account.current_balance) }}
                                </p>
                            </CardContent>
                            <CardFooter>
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-xs text-muted-foreground">Include in dashboard</span>
                                    <Switch :checked="account.include_in_dashboard"
                                        @update:checked="toggleDashboardInclusion(account)" />
                                </div>
                            </CardFooter>
                        </Card>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:px-4">
                        <Card>
                            <CardHeader class="pb-2">
                                <h3 class="font-semibold text-sm">Saldo Atual</h3>
                            </CardHeader>
                            <CardContent>
                                <p class="text-2xl font-bold">
                                    {{ new Intl.NumberFormat('pt-PT', {
                                        style: 'currency', currency: 'EUR'
                                    }).format(totalCurrentBalance) }}
                                </p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader class="pb-2">
                                <h3 class="font-semibold text-sm">Saldo Previsto</h3>
                            </CardHeader>
                            <CardContent>
                                <p class="text-2xl font-bold flex justify-between items-center">
                                    {{ new Intl.NumberFormat('pt-PT', {
                                        style: 'currency', currency: 'EUR'
                                    }).format(totalPredictedBalance) }}
                                    <HandCoins class="text-white bg-black rounded-full" />
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

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
