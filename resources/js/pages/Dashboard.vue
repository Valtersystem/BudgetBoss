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
        credit_card_expenses: number;
    };
    filters?: {
        year: number;
        month: number;
    };
}>(), {
    stats: () => ({
        current_balance: 0,
        monthly_incomes: 0,
        monthly_expenses: 0,
        credit_card_expenses: 0,
    }),
    filters: () => ({
        year: new Date().getFullYear(),
        month: new Date().getMonth() + 1,
    }),
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

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Toolbar -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Dashboard</h1>
                 <!-- Seletor de data -->
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
                                <!-- Navegador de Ano -->
                                <div class="mb-4 flex items-center justify-between">
                                    <Button @click="changePickerYear('prev')" variant="ghost" size="icon" class="h-8 w-8 hover:bg-white/10">
                                        <Icon name="chevron-left" class="h-5 w-5" />
                                    </Button>
                                    <span class="font-semibold">{{ pickerYear }}</span>
                                    <Button @click="changePickerYear('next')" variant="ghost" size="icon" class="h-8 w-8 hover:bg-white/10">
                                        <Icon name="chevron-right" class="h-5 w-5" />
                                    </Button>
                                </div>

                                <!-- Grade de Meses -->
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

                                <!-- Ações -->
                                <div class="mt-4 flex justify-between border-t border-white/10 pt-3">
                                    <Button variant="ghost" @click="isDatePickerOpen = false" class="hover:bg-white/10">Cancelar</Button>
                                    <Button variant="ghost" @click="goToCurrentMonth" class="hover:bg-white/10">Mês Atual</Button>
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
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                 <!-- Current Balance -->
                <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Saldo Atual</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.current_balance, user.currency) }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                            <Icon name="landmark" />
                        </div>
                    </div>
                </div>
                 <!-- Incomes -->
                <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Receitas</p>
                            <p class="text-2xl font-bold text-emerald-500">{{ formatCurrency(stats.monthly_incomes, user.currency) }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900 dark:text-emerald-300">
                            <Icon name="arrow-up" />
                        </div>
                    </div>
                </div>
                 <!-- Expenses -->
                <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Despesas</p>
                            <p class="text-2xl font-bold text-red-500">{{ formatCurrency(stats.monthly_expenses, user.currency) }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300">
                            <Icon name="arrow-down" />
                        </div>
                    </div>
                </div>
                 <!-- Credit Card -->
                <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cartão de Crédito</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.credit_card_expenses, user.currency) }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-900 dark:text-cyan-300">
                            <Icon name="credit-card" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adicione aqui outros gráficos e tabelas -->
        </div>
    </AppLayout>
</template>

