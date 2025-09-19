<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import Modal from '@/components/transaction/Modal.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/currency';
import type { BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { format, parseISO, isValid as isValidDate, addMonths, subMonths } from 'date-fns';
import { enUS } from 'date-fns/locale';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    transactions: App.Models.Paginated<App.Models.Transaction>;
    accounts: App.Models.Account[];
    categories: App.Models.Category[];
    tags: App.Models.Tag[];
    stats: {
        receivedIncomes: number;
        outstandingIncomes: number;
        totalIncomes: number;
        paidExpenses: number;
        outstandingExpenses: number;
        totalExpenses: number;
    };
    filters: {
        month: number;
        year: number;
        type: 'all' | 'income' | 'expense';
    };
}>();

const transactions = computed(() => props.transactions.data);
const includeFixed = ref(true);

// --- Data Fetching Logic ---
const fetchTransactions = () => {
    router.get(route('transactions.index'), {
        year: currentDate.value.getFullYear(),
        month: currentDate.value.getMonth() + 1,
        type: selectedType.value,
        include_fixed: includeFixed.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};


// --- Date filter logic ---
const currentDate = computed(() => new Date(props.filters.year, props.filters.month - 1));
const formattedDate = computed(() => {
    return format(currentDate.value, 'MMMM yyyy', { locale: enUS });
});

const changeMonth = (direction: 'prev' | 'next') => {
    const newDate = direction === 'prev'
        ? subMonths(currentDate.value, 1)
        : addMonths(currentDate.value, 1);

    selectDate(newDate.getMonth() + 1, newDate.getFullYear());
};

// --- Date picker logic (Popover) ---
const isDatePickerOpen = ref(false);
const pickerYear = ref(props.filters.year);
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

watch(() => props.filters.year, (newYear) => {
    pickerYear.value = newYear;
});

const changePickerYear = (direction: 'prev' | 'next') => {
    pickerYear.value += direction === 'prev' ? -1 : 1;
};

const selectDate = (month: number, year: number) => {
    router.get(route('transactions.index'), {
        year: year,
        month: month,
        type: selectedType.value,
        include_fixed: includeFixed.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
    isDatePickerOpen.value = false; // Close popover
};

const goToCurrentMonth = () => {
    const now = new Date();
    selectDate(now.getMonth() + 1, now.getFullYear());
}
// --- End of date filter logic ---

// --- Transaction type filter logic ---
const selectedType = ref(props.filters.type || 'all');

const typeOptions = {
    all: { label: 'All Transactions', color: 'bg-blue-500', hover: 'hover:bg-blue-600', icon: 'list' },
    income: { label: 'Incomes', color: 'bg-emerald-600', hover: 'hover:bg-emerald-700', icon: 'arrow-up-circle' },
    expense: { label: 'Expenses', color: 'bg-red-600', hover: 'hover:bg-red-700', icon: 'arrow-down-circle' },
};

const currentTypeOption = computed(() => typeOptions[selectedType.value]);

watch(selectedType, () => {
    fetchTransactions();
});

watch(includeFixed, () => {
    fetchTransactions();
});


watch(() => props.filters.type, (newType) => {
    selectedType.value = newType || 'all';
});
// --- End of transaction type filter logic ---


const user = usePage().props.auth.user as unknown as App.Models.User;
const breadcrumbItems: BreadcrumbItem[] = [{ title: 'Transactions', href: '/transactions' }];

const isModalOpen = ref(false);
const transactionToEdit = ref<App.Models.Transaction | null>(null);
const newTransactionType = ref<'income' | 'expense'>('expense');

// --- Modal Logic ---
watch(isModalOpen, (isOpen) => {
    if (!isOpen) {
        transactionToEdit.value = null;
    }
});

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
            console.error("Error deleting transaction:", errors);
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

        <div class="flex flex-col xl:flex-row">
            <!-- Main Content -->
            <div class="flex-1">
                <!-- Toolbar -->
                <div class="relative z-10 flex w-full flex-col items-center justify-between gap-4 p-4 sm:flex-row">
                    <div
                        class="flex flex-col items-stretch w-full gap-2 sm:flex-row sm:items-center sm:w-auto md:gap-4">
                        <!-- Dropdown for transaction type -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    :class="['w-full sm:w-48 justify-start text-left font-semibold text-white capitalize', currentTypeOption.color, currentTypeOption.hover]">
                                    <Icon :name="currentTypeOption.icon" class="mr-2 h-5 w-5" />
                                    <span>{{ currentTypeOption.label }}</span>
                                    <Icon name="chevron-down" class="ml-auto h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="w-48">
                                <DropdownMenuItem @click="selectedType = 'all'">
                                    <Icon name="list" class="mr-2 h-4 w-4" />
                                    <span>All Transactions</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="selectedType = 'income'">
                                    <Icon name="arrow-up-circle" class="mr-2 h-4 w-4 text-emerald-500" />
                                    <span>Incomes</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="selectedType = 'expense'">
                                    <Icon name="arrow-down-circle" class="mr-2 h-4 w-4 text-red-500" />
                                    <span>Expenses</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- Date Selector -->
                        <div
                            class="flex flex-row justify-center items-center gap-2 rounded-lg border border-white/10 bg-white/5 p-1 dark:bg-neutral-900">
                            <Button @click="changeMonth('prev')" variant="ghost" size="icon" class="h-8 w-8 text-white">
                                <Icon name="chevron-left" class="h-5 w-5" />
                            </Button>

                            <Popover v-model:open="isDatePickerOpen">
                                <PopoverTrigger as-child>
                                    <Button variant="ghost"
                                        class="w-32 text-center font-semibold capitalize text-white hover:bg-white/10">
                                        {{ formattedDate }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto border-neutral-800 bg-neutral-900 p-0 text-white"
                                    align="start">
                                    <div class="w-72 rounded-lg p-4 shadow-lg">
                                        <!-- Year Navigator -->
                                        <div class="mb-4 flex items-center justify-between">
                                            <Button @click="changePickerYear('prev')" variant="ghost" size="icon"
                                                class="h-8 w-8 hover:bg-white/10">
                                                <Icon name="chevron-left" class="h-5 w-5" />
                                            </Button>
                                            <span class="font-semibold">{{ pickerYear }}</span>
                                            <Button @click="changePickerYear('next')" variant="ghost" size="icon"
                                                class="h-8 w-8 hover:bg-white/10">
                                                <Icon name="chevron-right" class="h-5 w-5" />
                                            </Button>
                                        </div>

                                        <!-- Month Grid -->
                                        <div class="grid grid-cols-4 gap-2">
                                            <Button v-for="(month, index) in months" :key="month"
                                                @click="selectDate(index + 1, pickerYear)" variant="ghost"
                                                class="h-10 justify-center text-center hover:bg-white/10"
                                                :class="{ 'bg-violet-600 hover:bg-violet-700 text-white': index + 1 === props.filters.month && pickerYear === props.filters.year }">
                                                {{ month }}
                                            </Button>
                                        </div>

                                        <!-- Actions -->
                                        <div class="mt-4 flex justify-between border-t border-white/10 pt-3">
                                            <Button variant="ghost" @click="isDatePickerOpen = false"
                                                class="hover:bg-white/10">Cancel</Button>
                                            <Button variant="ghost" @click="goToCurrentMonth"
                                                class="hover:bg-white/10">Current Month</Button>
                                        </div>
                                    </div>
                                </PopoverContent>
                            </Popover>

                            <Button @click="changeMonth('next')" variant="ghost" size="icon" class="h-8 w-8 text-white">
                                <Icon name="chevron-right" class="h-5 w-5" />
                            </Button>
                        </div>
                    </div>


                    <div class="flex flex-col w-full gap-2 sm:flex-row sm:w-auto">
                        <Button @click="openAddModal('expense')"
                            class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white cursor-pointer"
                            variant="destructive">
                            <Icon name="trending-down" class="mr-2 h-4 w-4" />
                            New Expense
                        </Button>
                        <Button @click="openAddModal('income')"
                            class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer ">
                            <Icon name="trending-up" class="mr-2 h-4 w-4" />
                            New Income
                        </Button>
                    </div>
                </div>

                <!-- Table -->
                <div class="p-4 pt-0">
                    <div
                        class="overflow-hidden rounded-xl border border-white/10 bg-white shadow-sm dark:bg-neutral-900">
                        <Table>
                            <TableHeader class="bg-gray-50 dark:bg-neutral-950/50">
                                <TableRow>
                                    <TableHead class="w-[140px] dark:text-white">Date</TableHead>
                                    <TableHead class="dark:text-white">Description</TableHead>
                                    <TableHead class="hidden sm:table-cell dark:text-white">Category</TableHead>
                                    <TableHead class="hidden md:table-cell dark:text-white">Account</TableHead>
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
                                                <Icon v-if="transaction.is_fixed" name="pin"
                                                    class="h-4 w-4 shrink-0 text-gray-500"
                                                    v-tooltip="'Fixed transaction'" />
                                                <span class="font-medium">{{ transaction.description }}</span>
                                                <span v-if="transaction.is_paid"
                                                    class="hidden rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-400 sm:inline">paid</span>
                                                <span v-else
                                                    class="hidden rounded-full bg-amber-500/10 px-2 py-0.5 text-[11px] font-medium text-amber-400 sm:inline">pending</span>
                                            </div>
                                        </TableCell>

                                        <TableCell class="hidden sm:table-cell">
                                            <div v-if="transaction.category" class="inline-flex items-center gap-2">
                                                <span
                                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full"
                                                    :style="{ backgroundColor: transaction.category.color }">
                                                    <Icon :name="transaction.category.icon"
                                                        class="h-4 w-4 text-white" />
                                                </span>
                                                <span>{{ transaction.category.name }}</span>
                                            </div>
                                        </TableCell>

                                        <TableCell class="hidden md:table-cell text-sm opacity-90">{{
                                            transaction.account.name }}</TableCell>

                                        <TableCell class="text-right font-mono">
                                            <span
                                                :class="transaction.type === 'income' ? 'text-emerald-400' : 'text-rose-400'">
                                                {{ transaction.type === 'income' ? '+' : '-' }} {{
                                                    formatCurrency(transaction.value, user.currency) }}
                                            </span>
                                        </TableCell>

                                        <TableCell class="text-right">
                                            <div
                                                class="flex justify-end gap-1 opacity-60 transition group-hover:opacity-100">
                                                <Button variant="ghost" size="icon" class="h-8 w-8"
                                                    @click="openEditModal(transaction)">
                                                    <Icon name="pencil" class="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon"
                                                    @click="deleteTransaction(transaction)"
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
                                                <div class="text-sm">No transactions found for this month.</div>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>

            <!-- Stats Sidebar -->
            <aside class="w-full xl:w-80 lg:w-96 p-4">
                <div class="space-y-4">
                    <!-- Income Stats -->
                    <div v-if="selectedType === 'income' || selectedType === 'all'" class="space-y-2">
                        <div class="rounded-xl border border-white/10 bg-neutral-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Pending Incomes</p>
                                    <p class="text-xl font-bold">{{ formatCurrency(stats.outstandingIncomes,
                                        user.currency) }}</p>
                                </div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/20 text-amber-400">
                                    <Icon name="arrow-up-circle" />
                                </div>
                            </div>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-neutral-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Received Incomes</p>
                                    <p class="text-xl font-bold">{{ formatCurrency(stats.receivedIncomes, user.currency)
                                        }}</p>
                                </div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                                    <Icon name="check-circle" />
                                </div>
                            </div>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-neutral-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Total Incomes</p>
                                    <p class="text-xl font-bold">{{ formatCurrency(stats.totalIncomes, user.currency) }}
                                    </p>
                                </div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-500/20 text-gray-400">
                                    <Icon name="scale" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Stats -->
                    <div v-if="selectedType === 'expense' || selectedType === 'all'" class="space-y-2">
                        <div class="rounded-xl border border-white/10 bg-neutral-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Pending Expenses</p>
                                    <p class="text-xl font-bold">{{ formatCurrency(stats.outstandingExpenses,
                                        user.currency) }}</p>
                                </div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/20 text-amber-400">
                                    <Icon name="arrow-down-circle" />
                                </div>
                            </div>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-neutral-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Paid Expenses</p>
                                    <p class="text-xl font-bold">{{ formatCurrency(stats.paidExpenses, user.currency) }}
                                    </p>
                                </div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/20 text-red-400">
                                    <Icon name="check-circle" />
                                </div>
                            </div>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-neutral-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Total Expenses</p>
                                    <p class="text-xl font-bold">{{ formatCurrency(stats.totalExpenses, user.currency)
                                        }}</p>
                                </div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-500/20 text-gray-400">
                                    <Icon name="scale" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
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

