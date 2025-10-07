<script setup lang='ts'>

import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import Modal from '@/components/transaction/Modal.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/currency';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { format, parseISO, addMonths, subMonths, isValid as isValidDate } from 'date-fns';
import { enUS } from 'date-fns/locale';

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
        type: 'expense';
    };
}>();

const transactions = computed(() => props.transactions.data);
const includeFixed = ref(true);
const currentDate = computed(() => new Date(props.filters.year, props.filters.month - 1));
const formattedDate = computed(() => format(currentDate.value, 'MMMM yyyy', { locale: enUS }));

function fetchTransactions() {
    router.get(route('transactions.expenses.index'), {
        year: currentDate.value.getFullYear(),
        month: currentDate.value.getMonth() + 1,
        include_fixed: includeFixed.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

watch(includeFixed, fetchTransactions);

const changeMonth = (dir: 'prev' | 'next') => {
    const newDate = dir === 'prev' ? subMonths(currentDate.value, 1) : addMonths(currentDate.value, 1);
    selectDate(newDate.getMonth() + 1, newDate.getFullYear());
};

const isDatePickerOpen = ref(false);
const pickerYear = ref(props.filters.year);
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function changePickerYear(dir: 'prev' | 'next') {
    pickerYear.value += dir === 'prev' ? -1 : 1;
}

function selectDate(month: number, year: number) {
    router.get(route('transactions.expenses.index'), { year, month, include_fixed: includeFixed.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
    isDatePickerOpen.value = false;
}

function goToCurrentMonth() {
    const now = new Date();
    selectDate(now.getMonth() + 1, now.getFullYear());
}

const user = usePage().props.auth.user as unknown as App.Models.User;
const breadcrumbItems = [{ title: 'Expenses', href: '/transactions/expenses' }];

const isModalOpen = ref(false);
const transactionToEdit = ref<App.Models.Transaction | null>(null);

function openAddModal() {
    transactionToEdit.value = null;
    isModalOpen.value = true;
}
function openEditModal(t: App.Models.Transaction) {
    transactionToEdit.value = t;
    isModalOpen.value = true;
}
function deleteTransaction(t: App.Models.Transaction) {
    router.delete(route('transactions.destroy', t.id), {
        preserveState: true,
        preserveScroll: true,
    });
}

function prettyDate(dateStr: string): string {
    if (!dateStr) return '—';
    try {
        const d = parseISO(dateStr);
        if (isValidDate(d)) return format(d, 'dd MMM yyyy');
    } catch {
        return dateStr.split('T')[0];
    }
    return dateStr;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Expenses" />

        <div class="space-y-6 p-4">
            <!-- Estatísticas -->
            <aside class="grid gap-4 sm:grid-cols-3">
                <div v-for="(item, i) in [
                    { label: 'Pending Expenses', value: stats.outstandingExpenses, color: 'amber', icon: 'arrow-down-circle' },
                    { label: 'Paid Expenses', value: stats.paidExpenses, color: 'red', icon: 'check-circle' },
                    { label: 'Total Expenses', value: stats.totalExpenses, color: 'gray', icon: 'scale' }
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

            <!-- Cabeçalho e ações -->
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 p-1">
                    <Button @click="changeMonth('prev')" variant="ghost" size="icon" class="text-white">
                        <Icon name="chevron-left" />
                    </Button>
                    <Popover v-model:open="isDatePickerOpen">
                        <PopoverTrigger as-child>
                            <Button variant="ghost" class="w-32 font-semibold text-white capitalize hover:bg-white/10">
                                {{ formattedDate }}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto bg-neutral-900 border border-neutral-800 text-white"
                            align="start">
                            <div class="w-72 p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <Button @click="changePickerYear('prev')" variant="ghost" size="icon">
                                        <Icon name="chevron-left" />
                                    </Button>
                                    <span class="font-semibold">{{ pickerYear }}</span>
                                    <Button @click="changePickerYear('next')" variant="ghost" size="icon">
                                        <Icon name="chevron-right" />
                                    </Button>
                                </div>
                                <div class="grid grid-cols-4 gap-2">
                                    <Button v-for="(m, i) in months" :key="m" @click="selectDate(i + 1, pickerYear)"
                                        variant="ghost" class="hover:bg-white/10"
                                        :class="{ 'bg-violet-600 text-white': i + 1 === props.filters.month && pickerYear === props.filters.year }">
                                        {{ m }}
                                    </Button>
                                </div>
                                <Button variant="ghost" @click="goToCurrentMonth" class="hover:bg-white/10">
                                    Current Month
                                </Button>
                            </div>
                        </PopoverContent>
                    </Popover>
                    <Button @click="changeMonth('next')" variant="ghost" size="icon" class="text-white">
                        <Icon name="chevron-right" />
                    </Button>
                </div>

                <Button @click="openAddModal" class="bg-red-600 hover:bg-red-700 text-white">
                    <Icon name="trending-down" class="mr-2 h-4 w-4" /> New Expense
                </Button>
            </div>

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
                            <TableRow v-for="t in transactions" :key="t.id" class="hover:bg-white/5">
                                <TableCell>{{ prettyDate(t.date as string) }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <Icon v-if="t.is_fixed" name="pin" class="text-gray-500" />
                                        <span class="font-medium">{{ t.description }}</span>
                                        <span v-if="t.is_paid"
                                            class="hidden sm:inline rounded-full bg-emerald-500/10 px-2 text-xs text-emerald-400">paid</span>
                                        <span v-else
                                            class="hidden sm:inline rounded-full bg-amber-500/10 px-2 text-xs text-amber-400">pending</span>
                                    </div>
                                </TableCell>
                                <TableCell class="hidden sm:table-cell">
                                    <div v-if="t.category" class="flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 justify-center items-center rounded-full"
                                            :style="{ backgroundColor: t.category.color }">
                                            <Icon :name="t.category.icon" class="text-white h-4 w-4" />
                                        </span>
                                        {{ t.category.name }}
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell text-sm">{{ t.account.name }}</TableCell>
                                <TableCell class="text-right text-rose-400 font-mono">- {{ formatCurrency(t.value,
                                    user.currency) }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <Button variant="ghost" size="icon" @click="openEditModal(t)">
                                            <Icon name="pencil" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="text-rose-400 hover:text-rose-300"
                                            @click="deleteTransaction(t)">
                                            <Icon name="trash-2" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="6" class="py-12 text-center text-gray-400">
                                    <Icon name="inbox" class="mx-auto h-6 w-6 mb-2 opacity-60" />
                                    No expenses found for this month.
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Modal v-model="isModalOpen" :transaction="transactionToEdit" type="expense" :accounts="accounts"
            :categories="categories" :tags="tags" />
    </AppLayout>
</template>

<style scoped>
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
