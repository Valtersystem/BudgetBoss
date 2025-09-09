<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatCurrencyInput } from '@/lib/currency';
import { colors } from '@/lib/icons-and-colors';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    accounts: { type: Array as () => App.Models.Account[], required: true },
    bankInstitutions: { type: Array as () => App.Models.BankInstitution[], required: true },
});

const user = usePage().props.auth.user as unknown as App.Models.User;

const breadcrumbItems: BreadcrumbItem[] = [{ title: 'Accounts', href: '/accounts' }];

const form = useForm({
    id: null as number | null,
    name: '',
    bank_institution_id: null as number | null,
    initial_balance: 0,
    source_of_money: '',
    description: '',
    color: colors[0],
    dashboard: Boolean(),
});

const dashboardBalance = computed(() => props.accounts.filter(a => a.dashboard).reduce((s, a) => s + a.initial_balance, 0));
const totalBalance = computed(() => props.accounts.reduce((s, a) => s + a.initial_balance, 0));

const isAddEditModalOpen = ref(false);
const primaryColors = computed(() => colors.slice(0, 8));
const otherColors = computed(() => colors.slice(8));

const sourceOfMoneyOptions = [
    { value: 'Checking account', label: 'Checking account', icon: 'landmark' },
    { value: 'Money', label: 'Money', icon: 'dollar-sign' },
    { value: 'Savings', label: 'Savings', icon: 'piggy-bank' },
    { value: 'Investments', label: 'Investments', icon: 'bar-chart-2' },
    { value: 'Others', label: 'Others', icon: 'more-horizontal' },
];

const balanceInput = ref('');
watch(balanceInput, (val) => {
    const rawDigits = val.replace(/\D/g, '');
    const n = Number(rawDigits);
    form.initial_balance = !isNaN(n) ? n / 100 : 0;
});

const openAddModal = () => {
    form.reset();
    balanceInput.value = '';
    isAddEditModalOpen.value = true;
};

const openEditModal = (account: App.Models.Account) => {
    form.id = account.id;
    form.name = account.name;
    form.bank_institution_id = account.bank_institution_id;
    form.initial_balance = account.initial_balance;
    form.source_of_money = account.source_of_money ?? '';
    form.description = account.description ?? '';
    form.color = account.color;
    form.dashboard = Boolean(account.dashboard);
    balanceInput.value = formatCurrency(account.initial_balance, user.currency).replace(/[^\d,.-]/g, '');
    isAddEditModalOpen.value = true;
};

const closeModal = () => {
    isAddEditModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (form.id) {
        form.put(route('accounts.update', { account: form.id }), { onSuccess: () => closeModal() });
    } else {
        form.post(route('accounts.store'), { onSuccess: () => closeModal() });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="My Accounts" />

        <div class="flex w-full items-center justify-between p-4">
            <h1 class="text-2xl font-semibold">My Accounts</h1>
            <Button @click="openAddModal">
                <Icon name="plus" class="mr-2 h-4 w-4" />
                New Account
            </Button>
        </div>

        <!-- KPI cards -->
        <div class="grid grid-cols-1 gap-6 p-4 md:grid-cols-3">
            <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Current balance</p>
                        <p class="text-2xl font-bold">{{ formatCurrency(dashboardBalance, user.currency) }}</p>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                        <Icon name="dollar-sign" />
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-white/10 bg-white p-4 shadow-sm dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Balance</p>
                        <p class="text-2xl font-bold">{{ formatCurrency(totalBalance, user.currency) }}</p>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300">
                        <Icon name="scale" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards list -->
        <div class="grid grid-cols-1 gap-6 p-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div v-for="account in props.accounts" :key="account.id"
                class="flex flex-col justify-between overflow-hidden rounded-xl border bg-white shadow-sm transition-all hover:shadow-md dark:border-white/10 dark:bg-neutral-900"
                :style="{ borderLeft: `4px solid ${account.color}` }">
                <div class="p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-neutral-800">
                            <Icon :name="account.bank_institution.icon ?? 'landmark'"
                                class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                        </span>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ account.bank_institution.name }}</p>
                            <h3 class="font-semibold">{{ account.name }}</h3>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Current Balance</p>
                        <p class="text-2xl font-bold">{{ formatCurrency(account.initial_balance, user.currency) }}</p>
                    </div>
                </div>
                <div
                    class="flex items-center justify-end border-t bg-gray-50 p-2 dark:border-white/10 dark:bg-neutral-950/40">
                    <Button variant="ghost" size="sm" @click="openEditModal(account)">
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                    </Button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Dialog :open="isAddEditModalOpen" @update:open="isAddEditModalOpen = $event">
            <DialogContent
 class="w-full sm:max-w-xl rounded-2xl h-[97vh] 2xl:h-auto flex flex-col overflow-auto
  [&::-webkit-scrollbar]:w-2
  [&::-webkit-scrollbar-track]:bg-gray-100
  [&::-webkit-scrollbar-thumb]:bg-gray-300
  dark:[&::-webkit-scrollbar-track]:bg-neutral-700
  dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500"
                @close-auto-focus="closeModal">
                <DialogHeader class="border-b border-white/10 pb-3">
                    <DialogTitle class="flex items-center gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/15 text-sky-400">
                            <Icon :name="form.id ? 'pencil' : 'credit-card'" class="h-5 w-5" />
                        </span>
                        {{ form.id ? 'Edit Account' : 'Register New Account' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit" class="pt-3">
                    <div class="grid grid-cols-1 gap-4 py-2 md:grid-cols-2">
                        <!-- Initial balance -->
                        <div class="md:col-span-2">
                            <Label for="initial_balance" class="text-sm text-white/70">Initial Balance</Label>
                            <div class="relative mt-1">
                                <span
                                    class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/40">{{
                                        user.currency || '€' }}</span>
                                <Input id="initial_balance" v-model="balanceInput" type="text" inputmode="decimal"
                                    placeholder="0.00"
                                    class="h-14 rounded-2xl border-white/10 bg-white/5 pl-9 text-2xl font-semibold tracking-tight"
                                    @input="(e: Event) => formatCurrencyInput(e, (val) => (balanceInput = val), user.currency)" />
                            </div>
                            <InputError :message="form.errors.initial_balance" class="mt-1" />
                        </div>

                        <!-- Account Name -->
                        <div class="md:col-span-2">
                            <Label for="name" class="text-sm text-white/70">Account Name</Label>
                            <Input id="name" v-model="form.name"
                                class="mt-2 h-10 rounded-xl border-white/10 bg-white/5 capitalize" />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>

                        <!-- Bank / Source -->
                        <div>
                            <Label for="bank_institution_id" class="text-sm text-white/70">Bank Institution</Label>
                            <Select v-model="form.bank_institution_id">
                                <SelectTrigger class="mt-2 h-10 rounded-xl border-white/10 bg-white/5 capitalize">
                                    <SelectValue placeholder="Select an institution" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="institution in bankInstitutions" :key="institution.id"
                                            :value="institution.id" class="capitalize">{{ institution.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.bank_institution_id" class="mt-1" />
                        </div>

                        <div>
                            <Label for="source_of_money" class="text-sm text-white/70">Source of Money</Label>
                            <Select v-model="form.source_of_money">
                                <SelectTrigger class="mt-2 h-10 rounded-xl border-white/10 bg-white/5">
                                    <SelectValue placeholder="Select a source" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="option in sourceOfMoneyOptions" :key="option.value"
                                            :value="option.value">
                                            <div class="flex items-center gap-2">
                                                <Icon :name="option.icon" class="h-4 w-4 text-gray-500" />
                                                <span>{{ option.label }}</span>
                                            </div>
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.source_of_money" class="mt-1" />
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <Label for="description" class="text-sm text-white/70">Description (Optional)</Label>
                            <Input id="description" v-model="form.description"
                                class="mt-2 h-10 rounded-xl border-white/10 bg-white/5" />
                            <InputError :message="form.errors.description" class="mt-1" />
                        </div>

                        <!-- Color -->
                        <div class="md:col-span-2">
                            <Label class="text-sm text-white/70">Color</Label>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <button v-for="color in primaryColors" :key="color" type="button"
                                    class="relative h-8 w-8 rounded-full ring-1 ring-inset ring-white/10 transition hover:scale-110"
                                    :style="{ backgroundColor: color }" @click="form.color = color">
                                    <span v-if="form.color === color"
                                        class="absolute inset-0 rounded-full ring-2 ring-white"></span>
                                </button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button type="button" variant="outline" size="sm">Others</Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent class="p-2">
                                        <div class="grid grid-cols-6 gap-1">
                                            <button v-for="color in otherColors" :key="color" type="button"
                                                class="relative h-8 w-8 rounded-full ring-1 ring-inset ring-white/10 transition hover:scale-110"
                                                :style="{ backgroundColor: color }" @click="form.color = color">
                                                <span v-if="form.color === color"
                                                    class="absolute inset-0 rounded-full ring-2 ring-white"></span>
                                            </button>
                                        </div>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <InputError :message="form.errors.color" class="mt-1" />
                        </div>

                        <!-- Dashboard toggle -->
                        <div class="md:col-span-2 mt-2 flex items-center justify-between rounded-xl bg-white/5 p-3">
                            <div>
                                <Label for="dashboard-switch" class="text-sm">Include sum on dashboard</Label>
                                <p class="text-xs text-white/50">Show this account's balance in the top card.</p>
                            </div>
                            <Switch id="dashboard-switch" v-model="form.dashboard" />
                        </div>
                        <InputError :message="form.errors.dashboard" class="mt-1 md:col-span-2" />
                    </div>

                    <DialogFooter class="mt-6 gap-2">
                        <DialogClose as-child>
                            <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
                        </DialogClose>
                        <Button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700">
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style>
.dark input[type='date']::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
</style>
