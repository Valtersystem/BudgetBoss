<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
import Modal from '@/components/transaction/Modal.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/currency';
import type { BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { format, parseISO, isValid as isValidDate } from 'date-fns';
import { computed, ref } from 'vue';

const props = defineProps<{
  transactions: App.Models.Paginated<App.Models.Transaction>;
  accounts: App.Models.Account[];
  categories: App.Models.Category[];
  tags: App.Models.Tag[];
}>();

const transactions = computed(() => props.transactions.data);

const user = usePage().props.auth.user as unknown as App.Models.User;
const breadcrumbItems: BreadcrumbItem[] = [{ title: 'Transactions', href: '/transactions' }];

const isModalOpen = ref(false);
const transactionToEdit = ref<App.Models.Transaction | null>(null);
const newTransactionType = ref<'income' | 'expense'>('expense');

const openAddModal = (type: 'expense' | 'income') => {
    transactionToEdit.value = null;
    newTransactionType.value = type;
    isModalOpen.value = true;
};

const openEditModal = (transaction: App.Models.Transaction) => {
    transactionToEdit.value = transaction;
    isModalOpen.value = true;
}

const deleteTransaction = (t: any) => {
    router.delete(route('transactions.destroy', t.id), {
        preserveState: true,
        preserveScroll: true,
        onError: (errors) => {
            console.error("Erro ao deletar:", errors);
        }
    })
}

function prettyDate(dateStr: string): string {
    if (!dateStr) return '—';
    try {
        const date = parseISO(dateStr);
        if (isValidDate(date)) return format(date, 'dd MMM yyyy');
    } catch {
        return dateStr.split('T')[0];
    }
    return dateStr;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Transactions" />

        <!-- Toolbar -->
        <div class="flex w-full items-center justify-between p-4">
            <h1 class="text-2xl font-semibold">Transactions</h1>
            <div class="flex gap-2">
                <Button @click="openAddModal('expense')" class="bg-red-600 hover:bg-red-700 text-white cursor-pointer"
                    variant="destructive">
                    <Icon name="trending-down" class="mr-2 h-4 w-4" />
                    New Expense
                </Button>
                <Button @click="openAddModal('income')"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer ">
                    <Icon name="trending-up" class="mr-2 h-4 w-4" />
                    New Income
                </Button>
            </div>
        </div>

        <!-- Table -->
        <div class="p-4">
            <div class="overflow-hidden rounded-xl border border-white/10 bg-white shadow-sm dark:bg-neutral-900">
                <Table>
                    <TableHeader class="bg-gray-50 dark:bg-neutral-950/50">
                        <TableRow>
                            <TableHead class="w-[140px] dark:text-white">Date</TableHead>
                            <TableHead class="dark:text-white">Description</TableHead>
                            <TableHead class="dark:text-white">Category</TableHead>
                            <TableHead class="dark:text-white">Account</TableHead>
                            <TableHead class="text-right dark:text-white">Amount</TableHead>
                            <TableHead class="text-right dark:text-white">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="transactions.length > 0">
                            <TableRow v-for="transaction in transactions" :key="transaction.id"
                                class="group hover:bg-black/5 dark:hover:bg-white/5">
                                <TableCell class="font-mono text-[13px] opacity-80">{{
                                    prettyDate(transaction.date as unknown as string) }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <Icon v-if="transaction.is_fixed" name="pin" class="h-4 w-4 shrink-0 text-gray-500"
                                            v-tooltip="'Fixed transaction'" />
                                        <span class="font-medium">{{ transaction.description }}</span>
                                        <span v-if="transaction.is_paid"
                                            class="hidden rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-400 sm:inline">paid</span>
                                        <span v-else
                                            class="hidden rounded-full bg-amber-500/10 px-2 py-0.5 text-[11px] font-medium text-amber-400 sm:inline">pending</span>
                                    </div>
                                </TableCell>

                                <TableCell>
                                    <div v-if="transaction.category" class="inline-flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full"
                                            :style="{ backgroundColor: transaction.category.color }">
                                            <Icon :name="transaction.category.icon" class="h-4 w-4 text-white" />
                                        </span>
                                        <span>{{ transaction.category.name }}</span>
                                    </div>
                                </TableCell>

                                <TableCell class="text-sm opacity-90">{{ transaction.account.name }}</TableCell>

                                <TableCell class="text-right font-mono">
                                    <span :class="transaction.type === 'income' ? 'text-emerald-400' : 'text-rose-400'">
                                        {{ transaction.type === 'income' ? '+' : '-' }} {{
                                            formatCurrency(transaction.value, user.currency) }}
                                    </span>
                                </TableCell>

                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1 opacity-60 transition group-hover:opacity-100">
                                        <Button variant="ghost" size="icon" class="h-8 w-8"
                                            @click="openEditModal(transaction)">
                                            <Icon name="pencil" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="deleteTransaction(transaction)"
                                            class="h-8 w-8 text-rose-400 hover:text-rose-300">
                                            <Icon name="trash-2" class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>

                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="6" class="py-14">
                                    <div
                                        class="flex flex-col items-center gap-3 text-center text-gray-500 dark:text-gray-400">
                                        <Icon name="inbox" class="h-6 w-6 opacity-60" />
                                        <div class="text-sm">No transactions found.</div>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Modal v-model="isModalOpen" :transaction="transactionToEdit" :type="newTransactionType" :accounts="accounts"
            :categories="categories" :tags="tags" />

    </AppLayout>
</template>

<style>
.slide-fade-enter-active,
.slide-fade-leave-active {
    /* Use a consistent duration */
    transition: all 0.3s ease-out;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(16px);
}

.dark input[type='date']::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
</style>
