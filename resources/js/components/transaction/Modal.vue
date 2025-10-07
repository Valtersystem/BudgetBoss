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
import { useForm, usePage } from '@inertiajs/vue3';
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
    date: new Date().toISOString().split('T')[0],
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

// Função dedicada para popular o formulário, tornando o watcher mais limpo
const initializeForm = (transaction: App.Models.Transaction | null) => {
    if (transaction) {
        form.id = transaction.id;
        form.description = transaction.description;
        form.value = transaction.value;
        valueInput.value = (transaction.value / 100).toFixed(2).replace('.', ','); // Ajuste para formatação
        form.date = new Date(transaction.date).toISOString().split('T')[0];
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
        // Se houver campos opcionais, abre a seção "mais detalhes"
        if (transaction.tag_id || transaction.notes || transaction.is_fixed || transaction.is_recurring) {
            showMore.value = true;
        }
    } else {
        form.reset();
        valueInput.value = '';
        form.type = props.type;
        form.date = new Date().toISOString().split('T')[0];
    }
};

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        initializeForm(props.transaction);
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

    // Converte o valor do input para centavos antes de enviar
    const rawValue = parseFloat(valueInput.value.replace(/\./g, '').replace(',', '.'));
    form.value = Math.round(rawValue * 100);

    if (form.id) {
        form.put(route('transactions.update', form.id), options);
    } else {
        form.post(route('transactions.store'), options);
    }
};

const selectedDatePreset = computed(() => {
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);

    if (form.date === today.toISOString().split('T')[0]) return 'today';
    if (form.date === yesterday.toISOString().split('T')[0]) return 'yesterday';
    return null;
});

const setDatePreset = (preset: 'today' | 'yesterday') => {
    const newDate = preset === 'today' ? new Date() : new Date(Date.now() - 86400000);
    form.date = newDate.toISOString().split('T')[0];
};

watch(() => form.is_recurring, (isRecurring) => { if (isRecurring) form.is_fixed = false; });
watch(() => form.is_fixed, (isFixed) => { if (isFixed) form.is_recurring = false; });

const filteredCategories = computed(() => props.categories.filter((c) => c.type === form.type));

</script>

<template>
    <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
        <!-- A transição agora é controlada pelo grid-template-columns para um efeito mais suave -->
        <DialogContent class="w-full rounded-2xl flex flex-col overflow-auto transition-[grid-template-columns] duration-300 grid grid-cols-[1fr] md:grid-cols-[1fr_0px] data-[state=open]:md:data-[more=true]:grid-cols-[1fr_320px]" :data-more="showMore">
            <div class="flex flex-col min-w-0">
                <DialogHeader :class="['border-b pb-3', form.type === 'expense' ? 'border-rose-500/20' : 'border-emerald-500/20']">
                    <DialogTitle class="flex items-center gap-3">
                        <span :class="['inline-flex h-8 w-8 items-center justify-center rounded-xl', form.type === 'expense' ? 'bg-rose-500/15 text-rose-400' : 'bg-emerald-500/15 text-emerald-400']">
                            <Icon :name="form.type === 'expense' ? 'arrow-down-circle' : 'arrow-up-circle'" class="h-5 w-5" />
                        </span>
                        {{ form.id ? 'Edit Transaction' : form.type === 'expense' ? 'New Expense' : 'New Income' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit" class="flex-1 flex flex-col pt-2">
                    <div class="flex-1 space-y-5">
                        <!-- Value + Paid -->
                         <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <Label class="text-sm text-white/70" for="value">Value</Label>
                                <div class="relative mt-1">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/40">{{ user.currency }}</span>
                                    <Input id="value" v-model="valueInput" type="text" inputmode="decimal" placeholder="0,00" class="h-14 rounded-2xl border-white/10 bg-white/5 text-2xl font-semibold tracking-tight text-right pr-4 pl-10" required />
                                </div>
                                <InputError :message="form.errors.value" class="mt-1" />
                            </div>

                            <div>
                                <Label class="text-sm text-white/70" for="is_paid">{{ form.type === 'expense' ? 'Paid' : 'Received' }}</Label>
                                <div class="mt-1">
                                    <button type="button" :class="['inline-flex items-center justify-center gap-2 rounded-xl border px-4 text-sm transition h-14 min-w-[75px]', form.is_paid ? (form.type === 'expense' ? 'border-rose-400/30 bg-rose-500/15 text-rose-300' : 'border-emerald-400/30 bg-emerald-500/15 text-emerald-300') : 'border-white/10 bg-white/5 text-white/70']" @click="form.is_paid = !form.is_paid">
                                        <span class="h-2.5 w-2.5 rounded-full" :class="form.is_paid ? (form.type === 'expense' ? 'bg-rose-400' : 'bg-emerald-400') : 'bg-white/30'"></span>
                                        {{ form.is_paid ? 'Yes' : 'No' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Date -->
                        <div>
                            <Label class="text-sm text-white/70" for="date">Date</Label>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <Input id="date" v-model="form.date" type="date" class="h-10 rounded-xl border-white/10 bg-white/5 flex-1" required />
                                <div class="inline-flex overflow-hidden rounded-xl border-white/10 bg-white/5">
                                    <Button type="button" size="sm" variant="ghost" :class="['rounded-none', {'bg-violet-600 text-white hover:bg-violet-700': selectedDatePreset === 'today'}]" @click="setDatePreset('today')">Today</Button>
                                    <Button type="button" size="sm" variant="ghost" :class="['rounded-none', {'bg-violet-600 text-white hover:bg-violet-700': selectedDatePreset === 'yesterday'}]" @click="setDatePreset('yesterday')">Yesterday</Button>
                                </div>
                            </div>
                            <InputError :message="form.errors.date" class="mt-1" />
                        </div>

                        <!-- Description, Account, Category... (restante do formulário principal) -->
                         <div>
                            <Label class="text-sm text-white/70" for="description">Description</Label>
                            <Input id="description" v-model="form.description" class="mt-1 h-10 rounded-xl border-white/10 bg-white/5" required />
                            <InputError :message="form.errors.description" class="mt-1" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-sm text-white/70" for="account_id">Account</Label>
                                <Select v-model="form.account_id">
                                    <SelectTrigger class="mt-1 h-10 rounded-xl border-white/10 bg-white/5"><SelectValue placeholder="Select an account" /></SelectTrigger>
                                    <SelectContent><SelectGroup><SelectItem v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</SelectItem></SelectGroup></SelectContent>
                                </Select>
                                <InputError :message="form.errors.account_id" class="mt-1" />
                            </div>
                            <div>
                                <Label class="text-sm text-white/70" for="category_id">Category</Label>
                                <Select v-model="form.category_id">
                                    <SelectTrigger class="mt-1 h-10 rounded-xl border-white/10 bg-white/5"><SelectValue placeholder="Select a category" /></SelectTrigger>
                                    <SelectContent><SelectGroup><SelectItem v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem></SelectGroup></SelectContent>
                                </Select>
                                <InputError :message="form.errors.category_id" class="mt-1" />
                            </div>
                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="mt-6 flex items-center justify-between">
                         <Button type="button" variant="ghost" size="sm" @click="showMore = !showMore">
                            <Icon :name="showMore ? 'chevron-left' : 'chevron-right'" class="mr-1 h-4 w-4" />
                            {{ showMore ? 'Less details' : 'More details' }}
                        </Button>
                        <DialogFooter class="gap-2 flex-row">
                            <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
                            <Button type="submit" :disabled="form.processing" :class="form.type === 'expense' ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'">
                                {{ form.processing ? 'Saving...' : 'Save' }}
                            </Button>
                        </DialogFooter>
                    </div>
                </form>
            </div>

            <!-- SIDEBAR: Aparece com a transição -->
            <div class="overflow-y-auto overflow-x-hidden md:border-l md:border-white/10 md:pl-6">
                 <div v-if="showMore" class="space-y-4 pt-6 md:pt-0">
                    <div>
                        <Label class="text-sm text-white/70" for="tag_id">Tag (Optional)</Label>
                        <Select v-model="form.tag_id">
                             <SelectTrigger class="mt-1 h-10 rounded-xl border-white/10 bg-white/5"><SelectValue placeholder="Select a tag" /></SelectTrigger>
                             <SelectContent><SelectGroup><SelectItem :value="null">None</SelectItem><SelectItem v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.name }}</SelectItem></SelectGroup></SelectContent>
                        </Select>
                    </div>
                    <div>
                         <Label class="text-sm text-white/70" for="notes">Notes (Optional)</Label>
                         <Textarea id="notes" v-model="form.notes" class="mt-1 min-h-[90px] rounded-xl border-white/10 bg-white/5" />
                    </div>
                     <div class="space-y-4 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2"><Icon name="pin" class="h-5 w-5 text-gray-500" /><Label class="text-sm text-white/70" for="is_fixed">Fixed transaction</Label></span>
                            <Switch id="is_fixed" v-model:checked="form.is_fixed" :disabled="form.is_recurring" />
                        </div>
                         <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2"><Icon name="repeat" class="h-5 w-5 text-gray-500" /><Label class="text-sm text-white/70" for="is_recurring">Recurring transaction</Label></span>
                                <Switch id="is_recurring" v-model:checked="form.is_recurring" :disabled="form.is_fixed" />
                            </div>
                             <div v-if="form.is_recurring" class="mt-4 flex items-center gap-2">
                                <Input id="installments" v-model="form.installments" type="number" min="1" class="w-24 rounded-xl border-white/10 bg-white/5" />
                                <Select v-model="form.installment_period">
                                    <SelectTrigger class="h-10 rounded-xl border-white/10 bg-white/5"><SelectValue placeholder="Period" /></SelectTrigger>
                                    <SelectContent><SelectGroup><SelectItem value="days">Days</SelectItem><SelectItem value="weeks">Weeks</SelectItem><SelectItem value="months">Months</SelectItem><SelectItem value="years">Years</SelectItem></SelectGroup></SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
