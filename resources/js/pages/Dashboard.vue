<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { formatCurrency } from '@/lib/currency';
import { computed, ref, watch } from 'vue';
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { format, addMonths, subMonths } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const props = withDefaults(defineProps<{
    stats?: {
        current_balance: number;
        monthly_incomes: number;
        monthly_expenses: number;
        outstanding_incomes_total: number;
        outstanding_expenses_total: number;
    };
    filters?: {
        year: number;
        month: number;
    };
    incomesByCategory?: { name: string; color: string; total: number }[];
    expensesByCategory?: { name: string; color: string; total: number }[];
    expenseFrequency?: { date: string; total: number }[];
    incomesVsExpenses?: { name: string; incomes: number; expenses: number }[];
}>(), {
    stats: () => ({
        current_balance: 0,
        monthly_incomes: 0,
        monthly_expenses: 0,
        outstanding_incomes_total: 0,
        outstanding_expenses_total: 0,
    }),
    filters: () => ({
        year: new Date().getFullYear(),
        month: new Date().getMonth() + 1,
    }),
    incomesByCategory: () => [],
    expensesByCategory: () => [],
    expenseFrequency: () => [],
    incomesVsExpenses: () => [],
});

const user = usePage().props.auth.user as unknown as App.Models.User;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// --- Lógica do filtro de data ---
const currentDate = computed(() => new Date(props.filters.year, props.filters.month - 1));
const formattedDate = computed(() => format(currentDate.value, 'MMMM yyyy', { locale: ptBR }));

const changeMonth = (direction: 'prev' | 'next') => {
    const newDate = direction === 'prev'
        ? subMonths(currentDate.value, 1)
        : addMonths(currentDate.value, 1);

    selectDate(newDate.getMonth() + 1, newDate.getFullYear());
};

const isDatePickerOpen = ref(false);
const pickerYear = ref(props.filters.year);
const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

watch(() => props.filters.year, (newYear) => {
    pickerYear.value = newYear;
});

const changePickerYear = (direction: 'prev' | 'next') => {
    pickerYear.value += direction === 'prev' ? -1 : 1;
};

const selectDate = (month: number, year: number) => {
    router.get(route('dashboard'), {
        year: year,
        month: month,
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
// --- Fim da lógica do filtro de data ---

// --- Lógica dos Gráficos ---
const totalIncome = computed(() => props.incomesByCategory.reduce((sum, item) => sum + item.total, 0));
const totalExpense = computed(() => props.expensesByCategory.reduce((sum, item) => sum + item.total, 0));
const monthlyBalance = computed(() => props.stats.monthly_incomes - props.stats.monthly_expenses);

const balanceBarPercentage = computed(() => {
    if (props.stats.monthly_incomes <= 0) return 0;
    const percentage = (monthlyBalance.value / props.stats.monthly_incomes) * 100;
    return Math.max(0, Math.min(100, percentage));
});

const maxIncomesVsExpenses = computed(() => {
    if (!props.incomesVsExpenses || props.incomesVsExpenses.length === 0) return 1;
    const max = Math.max(...props.incomesVsExpenses.flatMap(item => [item.incomes, item.expenses]));
    return max === 0 ? 1 : max;
});

const maxExpenseFrequency = computed(() => {
    if (!props.expenseFrequency || props.expenseFrequency.length === 0) return 1;
    const max = Math.max(...props.expenseFrequency.map(item => item.total));
    return max === 0 ? 1 : max;
});

const expenseFrequencyPoints = computed(() => {
  if (!props.expenseFrequency || props.expenseFrequency.length < 2) return "0,100";
  const width = 280;
  const height = 100;
  const max = maxExpenseFrequency.value;
  return props.expenseFrequency.map((p, i) => {
    const x = (i / (props.expenseFrequency.length - 1)) * width;
    const y = height - (p.total / max) * height;
    return `${x},${y}`;
  }).join(' ');
});

const donutSegments = (categories: { total: number; color: string }[], total: number) => {
    if (total === 0) return [];
    let cumulativePercentage = 0;
    return categories.map(category => {
        const percentage = (category.total / total) * 100;
        const segment = {
            percentage,
            color: category.color,
            offset: -cumulativePercentage
        };
        cumulativePercentage += percentage;
        return segment;
    });
};

const expenseDonutSegments = computed(() => donutSegments(props.expensesByCategory, totalExpense.value));
const incomeDonutSegments = computed(() => donutSegments(props.incomesByCategory, totalIncome.value));

</script>

<template>
    <Head title="Dashboard" />
<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            <!-- Date Selector -->
            <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 p-1 dark:bg-neutral-900">
                <Button @click="changeMonth('prev')" variant="ghost" size="icon" class="h-8 w-8 text-white">
                    <Icon name="chevron-left" class="h-5 w-5" />
                </Button>

                <Popover v-model:open="isDatePickerOpen">
                    <PopoverTrigger as-child>
                        <Button variant="ghost" class="w-32 text-center font-semibold capitalize text-white hover:bg-white/10">
                            {{ formattedDate }}
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto border-neutral-800 bg-neutral-900 p-0 text-white" align="start">
                        <div class="w-72 rounded-lg p-4 shadow-lg">
                            <div class="mb-4 flex items-center justify-between">
                                <Button @click="changePickerYear('prev')" variant="ghost" size="icon" class="h-8 w-8 hover:bg-white/10">
                                    <Icon name="chevron-left" class="h-5 w-5" />
                                </Button>
                                <span class="font-semibold">{{ pickerYear }}</span>
                                <Button @click="changePickerYear('next')" variant="ghost" size="icon" class="h-8 w-8 hover:bg-white/10">
                                    <Icon name="chevron-right" class="h-5 w-5" />
                                </Button>
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <Button
                                    v-for="(month, index) in months"
                                    :key="month"
                                    @click="selectDate(index + 1, pickerYear)"
                                    variant="ghost"
                                    class="h-10 justify-center text-center hover:bg-white/10"
                                    :class="{ 'bg-violet-600 hover:bg-violet-700 text-white': index + 1 === props.filters.month && pickerYear === props.filters.year }"
                                >
                                    {{ month }}
                                </Button>
                            </div>
                            <div class="mt-4 flex justify-between border-t border-white/10 pt-3">
                                <Button variant="ghost" @click="isDatePickerOpen = false" class="hover:bg-white/10">Cancel</Button>
                                <Button variant="ghost" @click="goToCurrentMonth" class="hover:bg-white/10">Current Month</Button>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>

                <Button @click="changeMonth('next')" variant="ghost" size="icon" class="h-8 w-8 text-white">
                    <Icon name="chevron-right" class="h-5 w-5" />
                </Button>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Current Balance</p>
                        <p class="text-2xl font-bold">{{ formatCurrency(stats.current_balance, user.currency) }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                        <Icon name="landmark" />
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Incomes</p>
                        <p class="text-2xl font-bold text-emerald-500">{{ formatCurrency(stats.monthly_incomes, user.currency) }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900 dark:text-emerald-300">
                        <Icon name="arrow-up" />
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Expenses</p>
                        <p class="text-2xl font-bold text-red-500">{{ formatCurrency(stats.monthly_expenses, user.currency) }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300">
                        <Icon name="arrow-down" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Body -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3 flex flex-col gap-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Expenses by Category -->
                    <div class="rounded-xl border border-white/10 bg-white p-6 shadow-sm dark:bg-neutral-900 flex flex-col">
                        <h3 class="font-semibold text-lg mb-4">Expenses by category</h3>
                        <div v-if="expensesByCategory.length === 0" class="flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 flex-grow">
                            <Icon name="clock" class="h-8 w-8 opacity-50 mb-2" />
                            <p class="font-medium">No registered expenses this month.</p>
                        </div>
                        <div v-else class="flex-grow">
                            <div class="flex items-center justify-center h-40">
                                 <div class="relative w-36 h-36">
                                     <svg class="w-full h-full" viewBox="0 0 36 36" style="transform: rotate(-90deg) scaleY(-1);">
                                        <circle cx="18" cy="18" r="15.9155" class="stroke-current text-red-500/10" fill="transparent" stroke-width="4"></circle>
                                        <circle
                                            v-for="(segment, index) in expenseDonutSegments"
                                            :key="index"
                                            cx="18" cy="18" r="15.9155"
                                            fill="transparent"
                                            stroke-width="4"
                                            :stroke="segment.color"
                                            stroke-dasharray="100"
                                            :stroke-dashoffset="100 - segment.percentage"
                                            :style="{ 'stroke-dashoffset': 100 - segment.percentage, 'transform': `rotate(${segment.offset * 3.6}deg)` }"
                                            style="transform-origin: 50% 50%;"
                                        ></circle>
                                     </svg>
                                     <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-xl font-bold text-red-400">{{ formatCurrency(totalExpense, user.currency) }}</span>
                                        <span class="text-sm text-gray-400">Total</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 space-y-2 max-h-32 overflow-y-auto pr-2">
                                <div v-for="category in expensesByCategory" :key="category.name" class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="h-2 w-2 rounded-full flex-shrink-0" :style="{ backgroundColor: category.color }"></span>
                                        <span class="truncate">{{ category.name }}</span>
                                    </div>
                                    <span class="font-medium whitespace-nowrap">{{ formatCurrency(category.total, user.currency) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-auto pt-4">
                            <Button variant="link" class="text-violet-400 h-auto p-0">See More</Button>
                        </div>
                    </div>
                    <!-- Incomes by Category -->
                    <div class="rounded-xl border border-white/10 bg-white p-6 shadow-sm dark:bg-neutral-900 flex flex-col">
                        <h3 class="font-semibold text-lg mb-4">Incomes by category</h3>
                        <div v-if="incomesByCategory.length === 0" class="flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 flex-grow">
                            <Icon name="circle-off" class="h-8 w-8 opacity-50 mb-2" />
                            <p class="font-medium">No incomes registered this month.</p>
                        </div>
                         <div v-else class="flex-grow">
                            <div class="flex items-center justify-center h-40">
                                <div class="relative w-36 h-36">
                                     <svg class="w-full h-full" viewBox="0 0 36 36" style="transform: rotate(-90deg) scaleY(-1);">
                                        <circle cx="18" cy="18" r="15.9155" class="stroke-current text-violet-500/10" fill="transparent" stroke-width="4"></circle>
                                         <circle
                                            v-for="(segment, index) in incomeDonutSegments"
                                            :key="index"
                                            cx="18" cy="18" r="15.9155"
                                            fill="transparent"
                                            stroke-width="4"
                                            :stroke="segment.color"
                                            :stroke-dasharray="`${segment.percentage} 100`"
                                            :stroke-dashoffset="segment.offset"
                                            style="transform-origin: 50% 50%; transition: stroke-dashoffset 0.3s;"
                                        ></circle>
                                     </svg>
                                     <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-xl font-bold text-violet-400">{{ formatCurrency(totalIncome, user.currency) }}</span>
                                        <span class="text-sm text-gray-400">Total</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 space-y-2 max-h-32 overflow-y-auto pr-2">
                                <div v-for="category in incomesByCategory" :key="category.name" class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="h-2 w-2 rounded-full flex-shrink-0" :style="{ backgroundColor: category.color }"></span>
                                        <span class="truncate">{{ category.name }}</span>
                                    </div>
                                    <span class="font-medium whitespace-nowrap">{{ formatCurrency(category.total, user.currency) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-auto pt-4">
                            <Button variant="link" class="text-violet-400 h-auto p-0">See More</Button>
                        </div>
                    </div>
                </div>
                 <!-- Incomes x Expenses -->
                <div class="rounded-xl border border-white/10 bg-white p-6 shadow-sm dark:bg-neutral-900">
                    <h3 class="font-semibold text-lg mb-4">Incomes x Expenses</h3>
                    <div v-if="!incomesVsExpenses || incomesVsExpenses.every(d => d.incomes === 0 && d.expenses === 0)" class="flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 h-48">
                        <Icon name="line-chart" class="h-8 w-8 opacity-50 mb-2" />
                        <p class="font-medium">No data available.</p>
                    </div>
                    <div v-else class="h-48 flex justify-around items-end gap-2">
                        <div v-for="(monthData, index) in incomesVsExpenses" :key="index" class="flex flex-col items-center w-full h-full justify-end">
                            <div class="flex gap-1 items-end h-full w-full justify-center">
                                <div class="w-1/3 bg-emerald-500 rounded-t-sm" :style="{ height: `${(monthData.incomes / maxIncomesVsExpenses) * 100}%` }"></div>
                                <div class="w-1/3 bg-red-500 rounded-t-sm" :style="{ height: `${(monthData.expenses / maxIncomesVsExpenses) * 100}%` }"></div>
                            </div>
                            <span class="text-xs mt-2 text-gray-400">{{ monthData.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-2 flex flex-col gap-6">
                 <!-- Expense Frequency -->
                <div class="rounded-xl border border-white/10 bg-white p-6 shadow-sm dark:bg-neutral-900">
                    <div class="flex justify-between items-center mb-4">
                       <h3 class="font-semibold text-lg">Expense frequency</h3>
                       <Button variant="outline" size="sm" class="dark:bg-violet-500/20 dark:border-violet-500/40 dark:text-violet-300">Last 7 days</Button>
                   </div>
                    <div v-if="!expenseFrequency || expenseFrequency.every(d => d.total === 0)" class="flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 h-32">
                       <Icon name="bar-chart-2" class="h-8 w-8 opacity-50 mb-2 rotate-90" />
                       <p class="font-medium">No transactions in the last 7 days.</p>
                   </div>
                    <div v-else class="flex flex-col justify-between h-40">
                         <svg class="w-full h-full" viewBox="0 0 280 100" preserveAspectRatio="none">
                            <polyline :points="expenseFrequencyPoints" fill="rgba(139, 92, 246, 0.2)" stroke="#8B5CF6" stroke-width="2" />
                        </svg>
                        <div class="flex justify-between text-xs text-gray-400 mt-2">
                            <span v-for="item in expenseFrequency" :key="item.date" class="w-full text-center">{{ item.date }}</span>
                        </div>
                    </div>
                </div>
                 <!-- Monthly Balance -->
                <div class="rounded-xl border border-white/10 bg-white p-6 shadow-sm dark:bg-neutral-900">
                   <h3 class="font-semibold text-lg mb-4">Monthly balance</h3>
                   <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="relative h-16 w-4 bg-red-500/20 rounded-full overflow-hidden">
                               <div class="absolute bottom-0 left-0 w-full bg-emerald-500" :style="{ height: `${balanceBarPercentage}%` }"></div>
                           </div>
                           <div class="flex-1 space-y-2">
                               <div class="flex justify-between items-center text-sm">
                                   <span class="text-gray-400">Incomes</span>
                                   <span class="font-semibold text-emerald-400">{{ formatCurrency(stats.monthly_incomes, user.currency) }}</span>
                               </div>
                               <div class="flex justify-between items-center text-sm">
                                   <span class="text-gray-400">Expenses</span>
                                   <span class="font-semibold text-red-400">{{ formatCurrency(stats.monthly_expenses, user.currency) }}</span>
                               </div>
                                <div class="flex justify-between items-center text-sm pt-2 border-t border-white/10">
                                   <span class="text-gray-400">Balance</span>
                                   <span class="font-semibold">{{ formatCurrency(monthlyBalance, user.currency) }}</span>
                               </div>
                           </div>
                        </div>
                   </div>
                </div>
                 <!-- Pendencies and alerts -->
                <div class="rounded-xl border border-white/10 bg-white p-6 shadow-sm dark:bg-neutral-900">
                    <h3 class="font-semibold text-lg mb-4">Pendencies and alerts</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Outstanding expenses total</span>
                            <span class="font-semibold text-red-400">{{ formatCurrency(stats.outstanding_expenses_total, user.currency) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Outstanding incomes total</span>
                            <span class="font-semibold text-emerald-400">{{ formatCurrency(stats.outstanding_incomes_total, user.currency) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>

</template>

