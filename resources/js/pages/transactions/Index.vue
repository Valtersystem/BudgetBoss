<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import Modal from '@/components/Modal.vue';
import TransactionForm from './partials/TransactionForm.vue';
import { type BreadcrumbItem } from '@/types';


type SelectOption = { id: number; name: string; };
type Transaction = {
    id: number;
    description: string;
    amount: number;
    type: 'income' | 'expense';
    date: string;
    paid: boolean;
    account: SelectOption;
    category: SelectOption;
    tags: Array<SelectOption>;
    is_recurring: boolean | null;
    installments: number;
    frequency: string;
};

defineProps<{
    transactions: Array<Transaction>;
    accounts: Array<SelectOption>;
    categories: Array<SelectOption>;
    tags: Array<SelectOption>;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Transactions', href: '/transactions' },
];

const isModalOpen = ref(false);
const isUpdate = ref(false);

const form = useForm({
    id: null as number | null,
    description: '',
    amount: 0,
    type: 'expense' as 'income' | 'expense',
    date: new Date().toISOString().split('T')[0],
    paid: true as boolean,
    account_id: null as number | null,
    category_id: null as number | null,
    tags: [] as number[],
    is_recurring: false as boolean | null,
    installments: 2,
    frequency: 'monthly',
});

const openCreateModal = () => {
    isUpdate.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openUpdateModal = (transaction: Transaction) => {
    console.log('Opening update modal for transaction:', transaction);
    isUpdate.value = true;
    form.id = transaction.id;
    form.description = transaction.description;
    form.amount = transaction.amount;
    form.type = transaction.type;
    form.date = transaction.date;
    form.paid = transaction.paid;
    form.account_id = transaction.account.id;
    form.category_id = transaction.category.id;
    form.tags = (transaction.tags || []).map(tag => tag.id);
    form.is_recurring = transaction.is_recurring;
    form.installments = transaction.installments;
    form.frequency = transaction.frequency;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    const options = { onSuccess: () => closeModal() };
    if (isUpdate.value) {
        form.put(route('transactions.update', form.id), options);
    } else {
        form.post(route('transactions.store'), options);
    }
};

const destroy = (id: number) => {
    if (confirm('Are you sure you want to delete this transaction?')) {
        router.delete(route('transactions.destroy', id));
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Transactions" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading title="Transactions" />
                <Button @click="openCreateModal">New Transaction</Button>
            </div>

            <div class="border rounded-md">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr class="text-left align-middle">
                            <th class="p-4 font-medium">Description</th>
                            <th class="p-4 font-medium">Amount</th>
                            <th class="p-4 font-medium">Date</th>
                            <th class="p-4 font-medium">Account</th>
                            <th class="p-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="transactions.length === 0">
                            <td colspan="5" class="p-4 text-center text-muted-foreground">
                                No transactions found.
                            </td>
                        </tr>
                        <tr v-for="transaction in transactions" :key="transaction.id" class="border-b last:border-b-0">
                            <td class="p-4">
                                <div class="font-medium">{{ transaction.description }}</div>
                                <div class="text-xs text-muted-foreground">{{ transaction.category.name }}</div>
                            </td>
                            <td
                                :class="['p-4 font-semibold', transaction.type === 'income' ? 'text-green-600' : 'text-red-600']">
                                {{ new Intl.NumberFormat('pt-PT', {
                                    style: 'currency', currency: 'EUR'
                                }).format(transaction.amount) }}
                            </td>
                            <td class="p-4">{{ new Date(transaction.date).toLocaleDateString('pt-PT') }}</td>
                            <td class="p-4">{{ transaction.account.name }}</td>
                            <td class="p-4">
                                <div class="flex items-center justify-end">
                                    <Button @click="openUpdateModal(transaction)" variant="ghost" size="icon"
                                        class="h-8 w-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </Button>
                                    <Button @click="destroy(transaction.id)" variant="ghost" size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            <line x1="10" x2="10" y1="11" y2="17" />
                                            <line x1="14" x2="14" y1="11" y2="17" />
                                        </svg>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal" maxWidth="5xl" classMore="dark:bg-zinc-900">
            <div class="flex flex-col">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ isUpdate ? 'Edit Transaction' : 'New Transaction' }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Register your income or expense.
                    </p>
                </div>

                <TransactionForm v-model:form="form" :is-update="isUpdate" :accounts="accounts" :categories="categories"
                    :tags="tags" @submit="submit" />



                <div class="flex flex-row justify-end px-6 py-4  text-right">
                    <Button @click="submit" :disabled="form.processing">
                        {{ isUpdate ? 'Update Transaction' : 'Save Transaction' }}
                    </Button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
