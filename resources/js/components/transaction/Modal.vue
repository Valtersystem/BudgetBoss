<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { formatCurrencyInput } from '@/lib/currency';
import { useForm, usePage } from '@inertiajs/vue3';
import { format, isToday, isYesterday, parse, subDays, parseISO, isValid as isValidDate } from 'date-fns';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: boolean;
    transaction: App.Models.Transaction | null;
    type: 'income' | 'expense';
    accounts: App.Models.Account[];
    categories: App.Models.Category[];
    tags: App.Models.Tag[];
}>();



const emit = defineEmits(['update:modelValue']);

const user = usePage().props.auth.user as unknown as App.Models.User;
const showMore = ref(false);
const valueInput = ref('');

const isModalOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({
    id: null as number | null,
    description: '',
    value: 0,
    date: format(new Date(), 'yyyy-MM-dd'),
    type: 'expense' as 'income' | 'expense',
    is_paid: false,
    account_id: null as number | null,
    category_id: null as number | null,
    tag_id: null as number | null,
    notes: '',
    is_fixed: false,
    is_recurring: false,
    installments: 2,
    installment_period: 'months',
});

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        const t = props.transaction;
        if (t) {
            form.id = t.id;
            form.description = t.description;
            form.value = t.value;
            valueInput.value = String(t.value);
            form.date = format(parseISO(t.date as unknown as string), 'yyyy-MM-dd');
            form.type = t.type;
            form.is_paid = !!t.is_paid;
            form.account_id = t.account_id;
            form.category_id = t.category_id;
            form.tag_id = t.tag_id;
            form.notes = t.notes ?? '';
            form.is_fixed = !!t.is_fixed;
            form.is_recurring = !!t.is_recurring;
            form.installments = t.installments ?? 2;
            form.installment_period = t.installment_period ?? 'months';
        } else {
            form.reset();
            valueInput.value = '';
            form.type = props.type;
            form.date = format(new Date(), 'yyyy-MM-dd');
            form.is_paid = false;
        }
    } else {
        showMore.value = false;
    }
});


const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    const options = {
        onSuccess: () => closeModal(),
        preserveScroll: true
    };

    if (form.id) {
        console.log(form.data);
        form.put(route('transactions.update', form.id), options);
    } else {
        form.post(route('transactions.store'), options);
    }
};

const selectedDatePreset = computed(() => {
    if (!form.date) return null;
    const dateObj = parse(form.date, 'yyyy-MM-dd', new Date());
    if (!isValidDate(dateObj)) return null;

    if (isToday(dateObj)) return 'today';
    if (isYesterday(dateObj)) return 'yesterday';
    return null;
});


const setDatePreset = (preset: 'today' | 'yesterday') => {
    const newDate = preset === 'today' ? new Date() : subDays(new Date(), 1);
    form.date = format(newDate, 'yyyy-MM-dd');
};


watch(
    () => form.is_recurring,
    (isRecurring) => { if (isRecurring) form.is_fixed = false; },
);

watch(
    () => form.is_fixed,
    (isFixed) => { if (isFixed) form.is_recurring = false; },
);

watch(valueInput, (newValue) => {
    const rawDigits = newValue.replace(/\D/g, '');
    const numberValue = Number(rawDigits);
    form.value = !isNaN(numberValue) ? numberValue / 100 : 0;
});

const filteredCategories = computed(() => props.categories.filter((c) => c.type === form.type));

</script>

<template>
    <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
        <DialogContent  :class="[
    'w-full rounded-2xl h-[97vh] 2xl:h-auto flex flex-col overflow-auto transition-all duration-300',
    '[&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300',
    'dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500',
    showMore ? 'sm:max-w-4xl' : 'sm:max-w-xl'
  ]"
            @close-auto-focus="closeModal">
            <DialogHeader
                :class="['border-b pb-3', form.type === 'expense' ? 'border-rose-500/20' : 'border-emerald-500/20']">
                <DialogTitle class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl"
                        :class="form.type === 'expense' ? 'bg-rose-500/15 text-rose-400' : 'bg-emerald-500/15 text-emerald-400'">
                        <Icon :name="form.type === 'expense' ? 'arrow-down-circle' : 'arrow-up-circle'"
                            class="h-5 w-5" />
                    </span>
                    {{ form.id ? 'Edit Transaction' : form.type === 'expense' ? 'New Expense' : 'New Income' }}
                </DialogTitle>
            </DialogHeader>

            <!-- Form -->
            <form @submit.prevent="submit" class="flex h-full flex-col">
                <!-- Main container for columns -->
                <div class="flex-1 pt-2 md:flex md:gap-6">
                    <!-- MAIN COLUMN -->
                    <div class="space-y-5 md:flex-1">
                        <!-- Value + Paid -->
                        <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <Label class="text-sm text-white/70" for="value">Value</Label>
                                <div class="relative mt-1">
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-white/40">{{
                                            user.currency || '€' }}</span>
                                    <Input id="value" v-model="valueInput" type="text" inputmode="decimal"
                                        placeholder="0.00"
                                        class="h-14 rounded-2xl border-white/10 bg-white/5 text-2xl font-semibold tracking-tight"
                                        required
                                        @input="(e: Event) => formatCurrencyInput(e, (val: any) => (valueInput = val), user.currency)" />
                                </div>
                                <InputError :message="form.errors.value" class="mt-1" />
                            </div>

                            <div class="">
                                <Label class="text-sm text-white/70" for="is_paid">{{ form.type === 'expense' ?
                                    'Paid' :
                                    'Received' }}</Label>
                                <div class="mt-1 flex items-center gap-2 h-14">
                                    <button type="button" :class="['inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm transition h-14 min-w-[74.63px]',
                                        form.is_paid
                                            ? (form.type === 'expense' ? 'border-rose-400/30 bg-rose-500/15 text-rose-300' : 'border-emerald-400/30 bg-emerald-500/15 text-emerald-300')
                                            : 'border-white/10 bg-white/5 text-white/70'
                                    ]" @click="form.is_paid = !form.is_paid">
                                        <span class="h-2.5 w-2.5 rounded-full"
                                            :class="form.is_paid ? (form.type === 'expense' ? 'bg-rose-400' : 'bg-emerald-400') : 'bg-white/30'"></span>
                                        {{ form.is_paid ? 'Yes' : 'No ' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Date -->
                        <div>
                            <Label class="text-sm text-white/70" for="date">Date</Label>
                            <div class="mt-1 flex flex-wrap items-center gap-2 justify-between md:justify-start">
                                <div class="inline-flex overflow-hidden order rounded-xl border-white/10 bg-white/5">
                                    <Button type="button" size="lg" variant="ghost" :class="['rounded-none', {
                                        'bg-rose-600 text-white hover:bg-rose-700': selectedDatePreset === 'today' && form.type === 'expense',
                                        'bg-emerald-600 text-white hover:bg-emerald-700': selectedDatePreset === 'today' && form.type === 'income'
                                    }]" @click="setDatePreset('today')">Today</Button>
                                    <Button type="button" size="lg" variant="ghost" :class="['rounded-none', {
                                        'bg-rose-600 text-white hover:bg-rose-700': selectedDatePreset === 'yesterday' && form.type === 'expense',
                                        'bg-emerald-600 text-white hover:bg-emerald-700': selectedDatePreset === 'yesterday' && form.type === 'income'
                                    }]" @click="setDatePreset('yesterday')">Yesterday</Button>
                                </div>
                                <div class="relative">
                                    <Input id="date" v-model="form.date" type="date"
                                        class="h-10 rounded-xl border-white/10 bg-white/5" required />
                                    <Icon name="calendar"
                                        class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 opacity-50" />
                                </div>
                            </div>
                            <InputError :message="form.errors.date" class="mt-1" />
                        </div>

                        <!-- Description -->
                        <div>
                            <Label class="text-sm text-white/70" for="description">Description</Label>
                            <Input id="description" v-model="form.description"
                                class="mt-1 h-10 rounded-xl border-white/10 bg-white/5" required />
                            <InputError :message="form.errors.description" class="mt-1" />
                        </div>

                        <!-- Account & Category -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm text-white/70" for="account_id">Account</Label>
                                <Select v-model="form.account_id">
                                    <SelectTrigger class="mt-1 h-10 rounded-xl border-white/10 bg-white/5">
                                        <SelectValue placeholder="Select an account" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="acc in accounts" :key="acc.id" :value="acc.id">{{
                                                acc.name }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.account_id" class="mt-1" />
                            </div>

                            <div>
                                <Label class="text-sm text-white/70" for="category_id">Category</Label>
                                <Select v-model="form.category_id">
                                    <SelectTrigger class="mt-1 h-10 rounded-xl border-white/10 bg-white/5">
                                        <SelectValue placeholder="Select a category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">
                                                {{ cat.name }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.category_id" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- SIDEBAR WRAPPER: This animates the width -->
                    <div class="overflow-hidden transition-all duration-300 ease-in-out"
                        :style="{ 'width': showMore ? '320px' : '0' }">
                        <div class="md:w-[320px]">
                            <Transition name="slide-fade">
                                <div v-if="showMore" class="space-y-4 pt-6 md:border-l md:pt-0 md:pl-6">
                                    <div>
                                        <Label class="text-sm text-white/70" for="tag_id">Tag (Optional)</Label>
                                        <Select v-model="form.tag_id">
                                            <SelectTrigger class="mt-1 h-10 rounded-xl border-white/10 bg-white/5">
                                                <SelectValue placeholder="Select a tag" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem :value="null">None</SelectItem>
                                                    <SelectItem v-for="tag in tags" :key="tag.id" :value="tag.id">{{
                                                        tag.name }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="form.errors.tag_id" class="mt-1" />
                                    </div>

                                    <div>
                                        <Label class="text-sm text-white/70" for="notes">Notes (Optional)</Label>
                                        <Textarea id="notes" v-model="form.notes"
                                            class="mt-1 min-h-[90px] rounded-xl border-white/10 bg-white/5" />
                                        <InputError :message="form.errors.notes" class="mt-1" />
                                    </div>

                                    <div class="space-y-4 pt-2">
                                        <div class="flex items-center justify-between">
                                            <span class="flex items-center gap-2">
                                                <Icon name="pin" class="h-5 w-5 text-gray-500" />
                                                <Label class="text-sm text-white/70" for="is_fixed">Fixed
                                                    income/expense</Label>
                                            </span>
                                            <Switch id="is_fixed" v-model="form.is_fixed"
                                                :disabled="form.is_recurring" />
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="flex items-center gap-2">
                                                    <Icon name="repeat" class="h-5 w-5 text-gray-500" />
                                                    <Label class="text-sm text-white/70" for="is_recurring">Repeat
                                                        transaction</Label>
                                                </span>
                                                <Switch id="is_recurring" v-model="form.is_recurring"
                                                    :disabled="form.is_fixed" />
                                            </div>
                                            <Transition name="slide-fade">
                                                <div v-if="form.is_recurring" class="mt-4 flex items-center gap-2">
                                                    <Input id="installments" v-model="form.installments" type="number"
                                                        min="1" class="w-24 rounded-xl border-white/10 bg-white/5" />
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">times</span>
                                                    <Select v-model="form.installment_period">
                                                        <SelectTrigger
                                                            class="h-10 rounded-xl border-white/10 bg-white/5">
                                                            <SelectValue placeholder="Period" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                <SelectItem value="days">Days</SelectItem>
                                                                <SelectItem value="weeks">Weeks</SelectItem>
                                                                <SelectItem value="months">Months</SelectItem>
                                                                <SelectItem value="years">Years</SelectItem>
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                            </Transition>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="mt-6 flex items-center justify-between">
                    <Button type="button" variant="ghost" size="sm" @click="showMore = !showMore">
                        <Icon :name="showMore ? 'chevron-up' : 'chevron-down'" class="mr-1 h-4 w-4" />
                        {{ showMore ? 'Less details' : 'More details' }}
                    </Button>

                    <DialogFooter class="gap-2 flex flex-row md:block">
                        <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
                        <Button type="submit" :disabled="form.processing"
                            :class="form.type === 'expense' ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'">
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </Button>
                    </DialogFooter>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
