<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatCurrencyInput } from '@/lib/currency';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { format, isToday, isYesterday, parse, subDays } from 'date-fns';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    transactions: App.Models.Transaction;
    accounts: App.Models.Account[];
    categories: App.Models.Category[];
    tags: App.Models.Tag[];
}>();

const user = usePage().props.auth.user as unknown as App.Models.User;

const breadcrumbItems: BreadcrumbItem[] = [{ title: 'Transactions', href: '/transactions' }];
const selectedDatePreset = ref<'today' | 'yesterday' | null>('today');

const isAddEditModalOpen = ref(false);
const showMore = ref(false);

const form = useForm({
    id: null as number | null,
    description: '',
    value: 0,
    date: format(new Date(), 'yyyy-MM-dd'),
    type: 'expense' as 'income' | 'expense',
    is_paid: true,
    account_id: null as number | null,
    category_id: null as number | null,
    tag_id: null as number | null,
    notes: '',
    is_fixed: false,
    is_recurring: false,
    installments: 2,
    installment_period: 'months',
});

const setDatePreset = (preset: 'today' | 'yesterday') => {
    selectedDatePreset.value = preset;
    const newDate = preset === 'today' ? new Date() : subDays(new Date(), 1);
    form.date = format(newDate, 'yyyy-MM-dd');
};

watch(
    () => form.date,
    (newDate) => {
        if (!newDate) return;
        const dateObj = parse(newDate, 'yyyy-MM-dd', new Date());
        if (isToday(dateObj)) {
            selectedDatePreset.value = 'today';
        } else if (isYesterday(dateObj)) {
            selectedDatePreset.value = 'yesterday';
        } else {
            selectedDatePreset.value = null; // No button selected
        }
    },
    { immediate: true },
);

const filteredCategories = computed(() => {
    return props.categories.filter((category) => category.type === form.type);
});

const openAddModal = (type: 'expense' | 'income') => {
    form.reset();
    form.type = type;
    form.date = format(new Date(), 'yyyy-MM-dd');
    form.is_paid = true;
    isAddEditModalOpen.value = true;
};

const openEditModal = (transaction: any) => {
    form.id = transaction.id;
    form.description = transaction.description;
    form.value = transaction.value;
    form.date = transaction.date;
    form.type = transaction.type;
    form.is_paid = !!transaction.is_paid;
    form.account_id = transaction.account_id;
    form.category_id = transaction.category_id;
    form.tag_id = transaction.tag_id;
    form.notes = transaction.notes ?? '';
    form.is_fixed = !!transaction.is_fixed;
    form.is_recurring = !!transaction.is_recurring;
    form.installments = transaction.installments ?? 2;
    form.installment_period = transaction.installment_period ?? 'months';
    isAddEditModalOpen.value = true;
};

const closeModal = () => {
    isAddEditModalOpen.value = false;
};

const submit = () => {
    const options = {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    };
    if (form.id) {
        form.put(route('transactions.update', form.id), options);
    } else {
        form.post(route('transactions.store'), options);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Transactions" />

        <div class="flex w-full items-center justify-between p-4">
            <h1 class="text-2xl font-semibold">Transactions</h1>
            <div class="flex gap-2">
                <Button @click="openAddModal('expense')" variant="destructive">
                    <Icon name="plus" class="mr-2 h-4 w-4" />
                    New Expense
                </Button>
                <Button @click="openAddModal('income')" class="bg-green-600 hover:bg-green-700">
                    <Icon name="plus" class="mr-2 h-4 w-4" />
                    New Income
                </Button>
            </div>
        </div>

        <div class="p-4">
            <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
                <Table>
                    <TableHeader class="bg-gray-50 dark:bg-gray-900">
                        <TableRow>
                            <TableHead class="w-[100px] dark:text-white">Date</TableHead>
                            <TableHead class="dark:text-white">Description</TableHead>
                            <TableHead class="dark:text-white">Category</TableHead>
                            <TableHead class="dark:text-white">Account</TableHead>
                            <TableHead class="text-right dark:text-white">Amount</TableHead>
                            <TableHead class="text-right dark:text-white">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="transactions.data.length > 0">
                            <TableRow v-for="transaction in transactions.data" :key="transaction.id">
                                <TableCell>{{ transaction.date }}</TableCell>
                                <TableCell class="font-medium">{{ transaction.description }}</TableCell>
                                <TableCell>
                                    <span v-if="transaction.category" class="inline-flex items-center gap-2">
                                        <span
                                            class="flex h-6 w-6 items-center justify-center rounded-full"
                                            :style="{ backgroundColor: transaction.category.color }"
                                        >
                                            <Icon :name="transaction.category.icon" class="h-4 w-4 text-white" />
                                        </span>
                                        {{ transaction.category.name }}
                                    </span>
                                </TableCell>
                                <TableCell>{{ transaction.account.name }}</TableCell>
                                <TableCell class="text-right font-mono" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(transaction.value, user.currency) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="icon" @click="openEditModal(transaction)">
                                        <Icon name="pencil" class="h-4 w-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-400"> No transactions found. </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Dialog :open="isAddEditModalOpen" @update:open="isAddEditModalOpen = $event">
            <DialogContent :class="['w-full transition-all duration-300', showMore ? 'sm:max-w-4xl' : 'sm:max-w-lg']" @close-auto-focus="closeModal">
                <DialogHeader>
                    <DialogTitle>
                        {{ form.id ? 'Edit Transaction' : form.type === 'expense' ? 'New Expense' : 'New Income' }}
                    </DialogTitle>
                </DialogHeader>

                <!-- Layout dividido -->
                <form
                    @submit.prevent="submit"
                    :class="['gap-6 transition-all duration-300', showMore ? 'grid grid-cols-1 md:grid-cols-2' : 'grid grid-cols-1']"
                >
                    <!-- Coluna principal -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div>
                                <Label for="value">Value</Label>
                                <Input
                                    id="value"
                                    :model-value="form.value"
                                    type="text"
                                    inputmode="decimal"
                                    class="mt-1 text-left dark:text-2xl"
                                    placeholder="0.00"
                                    required
                                    @input="(e: Event) => formatCurrencyInput(e, (val) => (form.value = val), user.currency)"
                                />
                                <InputError :message="form.errors.value" class="mt-1" />
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-3.5">
                                <span class="flex items-center gap-2">
                                    <Icon :name="form.type === 'expense' ? 'arrow-down-circle' : 'arrow-up-circle'" class="h-5 w-5" />
                                    <Label for="is_paid">{{ form.type === 'expense' ? 'Paid' : 'Received' }}</Label>
                                </span>
                                <Switch id="is_paid" v-model:checked="form.is_paid" />
                            </div>
                        </div>
                        <div>
                            <Label for="date">Date</Label>
                            <div class="mt-1 flex flex-row items-center gap-2">
                                <Button
                                    type="button"
                                    :variant="selectedDatePreset === 'today' ? 'default' : 'outline'"
                                    @click="setDatePreset('today')"
                                >
                                    Today
                                </Button>
                                <Button
                                    type="button"
                                    :variant="selectedDatePreset === 'yesterday' ? 'default' : 'outline'"
                                    @click="setDatePreset('yesterday')"
                                >
                                    Yesterday
                                </Button>
                                <Input id="date" v-model="form.date" type="date" class="flex-grow" required />
                            </div>
                            <InputError :message="form.errors.date" class="mt-1" />
                        </div>
                        <div>
                            <Label for="description">Description</Label>
                            <Input id="description" v-model="form.description" class="mt-1" required />
                            <InputError :message="form.errors.description" class="mt-1" />
                        </div>

                        <div>
                            <Label for="account_id">Account</Label>
                            <Select v-model="form.account_id">
                                <SelectTrigger class="mt-1"><SelectValue placeholder="Select an account" /></SelectTrigger>
                                <SelectContent
                                    ><SelectGroup>
                                        <SelectItem v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</SelectItem>
                                    </SelectGroup></SelectContent
                                >
                            </Select>
                            <InputError :message="form.errors.account_id" class="mt-1" />
                        </div>

                        <div>
                            <Label for="category_id">Category</Label>
                            <Select v-model="form.category_id">
                                <SelectTrigger class="mt-1"><SelectValue placeholder="Select a category" /></SelectTrigger>
                                <SelectContent
                                    ><SelectGroup>
                                        <SelectItem v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
                                    </SelectGroup></SelectContent
                                >
                            </Select>
                            <InputError :message="form.errors.category_id" class="mt-1" />
                        </div>
                    </div>

                    <!-- Coluna lateral (more details) -->
                    <Transition name="slide-fade">
                        <div v-if="showMore" class="space-y-4 border-l pl-6">
                            <div>
                                <Label for="tag_id">Tag (Optional)</Label>
                                <Select v-model="form.tag_id">
                                    <SelectTrigger class="mt-1"><SelectValue placeholder="Select a tag" /></SelectTrigger>
                                    <SelectContent
                                        ><SelectGroup>
                                            <SelectItem :value="null">None</SelectItem>
                                            <SelectItem v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.name }}</SelectItem>
                                        </SelectGroup></SelectContent
                                    >
                                </Select>
                                <InputError :message="form.errors.tag_id" class="mt-1" />
                            </div>

                            <div>
                                <Label for="notes">Notes (Optional)</Label>
                                <Textarea id="notes" v-model="form.notes" class="mt-1" />
                                <InputError :message="form.errors.notes" class="mt-1" />
                            </div>

                            <div class="space-y-4 pt-4">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <Icon name="pin" class="h-5 w-5 text-gray-500" />
                                        <Label for="is_fixed">Fixed income/expense</Label>
                                    </span>
                                    <Switch id="is_fixed" v-model:checked="form.is_fixed" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <Icon name="repeat" class="h-5 w-5 text-gray-500" />
                                        <Label for="is_recurring">Repeat transaction</Label>
                                    </span>
                                    <Switch id="is_recurring" v-model:checked="form.is_recurring" />
                                </div>
                                <div v-if="form.is_recurring" class="flex items-center gap-2">
                                    <Input id="installments" v-model="form.installments" type="number" min="1" class="w-20" />
                                    <span class="text-sm text-gray-500 dark:text-gray-400">times</span>
                                    <Select v-model="form.installment_period">
                                        <SelectTrigger><SelectValue placeholder="Period" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem value="days">Days</SelectItem>
                                                <SelectItem value="months">Months</SelectItem>
                                                <SelectItem value="years">Years</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </div>
                    </Transition>

                    <!-- Botões -->
                    <div class="mt-6 flex items-center justify-between md:col-span-2">
                        <Button type="button" variant="ghost" size="sm" @click="showMore = !showMore">
                            {{ showMore ? 'Less details' : 'More details' }}
                        </Button>

                        <DialogFooter>
                            <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
                            <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Saving...' : 'Save' }}</Button>
                        </DialogFooter>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
<style>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
.dark input[type='date']::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
</style>

