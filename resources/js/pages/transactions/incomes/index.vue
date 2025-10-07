<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
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
        type: 'income';
    };
}>();

const transactions = computed(() => props.transactions.data);
const includeFixed = ref(true);

const fetchTransactions = () => {
    router.get(route('transactions.incomes.index'), {
        year: currentDate.value.getFullYear(),
        month: currentDate.value.getMonth() + 1,
        include_fixed: includeFixed.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(includeFixed, () => {
    fetchTransactions();
});

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
    router.get(route('transactions.incomes.index'), {
        year: year,
        month: month,
        include_fixed: includeFixed.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
    isDatePickerOpen.value = false;
};

const goToCurrentMonth = () => {
    const now = new Date();
    selectDate(now.getMonth() + 1, now.getFullYear());
}

const user = usePage().props.auth.user as unknown as App.Models.User;
const breadcrumbItems: BreadcrumbItem[] = [{ title: 'Incomes', href: '/transactions/incomes' }];

const isModalOpen = ref(false);
const transactionToEdit = ref<App.Models.Transaction | null>(null);

watch(isModalOpen, (isOpen) => {
    if (!isOpen) {
        transactionToEdit.value = null;
    }
});

const openAddModal = () => {
    transactionToEdit.value = null;
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
    });
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

        <Head title="Incomes" />

        <div class="space-y-6 p-4">
            <!-- Stats Section -->
            <aside class="grid gap-4 sm:grid-cols-3">
                <div v-for="(item, i) in [
                    { label: 'Pending Incomes', value: stats.outstandingIncomes, color: 'amber', icon: 'arrow-up-circle' },
                    { label: 'Received Incomes', value: stats.receivedIncomes, color: 'emerald', icon: 'check-circle' },
                    { label: 'Total Incomes', value: stats.totalIncomes, color: 'gray', icon: 'scale' }
                ]" :key="i" class="rounded-xl border border-white/10 bg-neutral-900 p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-400">{{ item.label }}</p>
                            <p class="text-xl font-bold">{{ formatCurrency(item.value, user.currency) }}</p>
                        </div>
                        <div
                            :class="`flex h-10 w-10 items-center justify-center rounded-full bg-${item.color}-500/20 text-${item.color}-400`">
                            <Icon :name="item.icon" />
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Header and Actions -->
            <div class="flex flex-wrap justify-between items-center gap-4">
                <!-- Date Selector -->
                <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 p-1">
                    <Button @click="changeMonth('prev')" variant="ghost" size="icon" class="h-8 w-8 text-white">
                        <Icon name="chevron-left" class="h-5 w-5" />
                    </Button>
                    <Popover v-model:open="isDatePickerOpen">
                        <PopoverTrigger as-child>
                            <Button variant="ghost" class="w-32 font-semibold text-white capitalize hover:bg-white/10">
                                {{ formattedDate }}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto bg-neutral-900 border border-neutral-800 text-white p-0"
                            align="start">
                            <div class="w-72 p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <Button @click="changePickerYear('prev')" variant="ghost" size="icon" class="h-8 w-8 hover:bg-white/10">
                                        <Icon name="chevron-left" class="h-5 w-5" />
                                    </Button>
                                    <span class="font-semibold">{{ pickerYear }}</span>
                                    <Button @click="changePickerYear('next')" variant="ghost" size="icon" class="h-8 w-8 hover:bg-white/10">
                                        <Icon name="chevron-right" class="h-5 w-5" />
                                    </Button>
                                </div>
                                <div class="grid grid-cols-4 gap-2">
                                    <Button v-for="(m, i) in months" :key="m" @click="selectDate(i + 1, pickerYear)"
                                        variant="ghost" class="h-10 justify-center text-center hover:bg-white/10"
                                        :class="{ 'bg-violet-600 hover:bg-violet-700 text-white': i + 1 === props.filters.month && pickerYear === props.filters.year }">
                                        {{ m }}
                                    </Button>
                                </div>
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

                <Button @click="openAddModal" class="bg-emerald-600 hover:bg-emerald-700 text-white">
                    <Icon name="trending-up" class="mr-2 h-4 w-4" /> New Income
                </Button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-white/10 bg-neutral-900">
                <Table>
                    <TableHeader class="bg-neutral-950/50">
                        <TableRow>
                            <TableHead class="text-white">Date</TableHead>
                            <TableHead class="text-white">Description</TableHead>
                            <TableHead class="text-white hidden sm:table-cell">Category</TableHead>
                            <TableHead class="text-white hidden md:table-cell">Account</TableHead>
                            <TableHead class="text-right text-white">Amount</TableHead>
                            <TableHead class="text-right text-white">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="transactions.length">
                            <TableRow v-for="transaction in transactions" :key="transaction.id" class="group hover:bg-white/5">
                                <TableCell class="font-mono text-[13px] opacity-80">{{ prettyDate(transaction.date as string) }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <Icon v-if="transaction.is_fixed" name="pin" class="h-4 w-4 shrink-0 text-gray-500" v-tooltip="'Fixed transaction'" />
                                        <span class="font-medium">{{ transaction.description }}</span>
                                        <span v-if="transaction.is_paid"
                                            class="hidden sm:inline rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-400">received</span>
                                        <span v-else
                                            class="hidden sm:inline rounded-full bg-amber-500/10 px-2 py-0.5 text-[11px] font-medium text-amber-400">pending</span>
                                    </div>
                                </TableCell>
                                <TableCell class="hidden sm:table-cell">
                                    <div v-if="transaction.category" class="inline-flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full"
                                            :style="{ backgroundColor: transaction.category.color }">
                                            <Icon :name="transaction.category.icon" class="h-4 w-4 text-white" />
                                        </span>
                                        <span>{{ transaction.category.name }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell text-sm opacity-90">{{ transaction.account.name }}</TableCell>
                                <TableCell class="text-right font-mono text-emerald-400">+ {{ formatCurrency(transaction.value, user.currency) }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1 opacity-60 transition group-hover:opacity-100">
                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditModal(transaction)">
                                            <Icon name="pencil" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-rose-400 hover:text-rose-300"
                                            @click="deleteTransaction(transaction)">
                                            <Icon name="trash-2" class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="6" class="py-14 text-center text-gray-400">
                                    <Icon name="inbox" class="mx-auto h-6 w-6 mb-2 opacity-60" />
                                    No incomes found for this month.
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Modal v-model="isModalOpen" :transaction="transactionToEdit" type="income" :accounts="accounts"
            :categories="categories" :tags="tags" />
    </AppLayout>
</template>

<style>
.slide-fade-enter-active,
.slide-fade-leave-active {
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
