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
import type { BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { format, isToday, isYesterday, parse, subDays, parseISO, isValid as isValidDate } from 'date-fns';
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
const valueInput = ref('');

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

// Observa o estado do modal e reinicia o formulário quando ele é fechado.
watch(isAddEditModalOpen, (isOpen) => {
  if (!isOpen) {
    form.reset();
    valueInput.value = '';
    showMore.value = false;
  }
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
    if (isToday(dateObj)) selectedDatePreset.value = 'today';
    else if (isYesterday(dateObj)) selectedDatePreset.value = 'yesterday';
    else selectedDatePreset.value = null;
  },
  { immediate: true },
);

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

const openAddModal = (type: 'expense' | 'income') => {
  // A reinicialização do formulário agora é tratada pelo watcher.
  form.type = type;
  form.date = format(new Date(), 'yyyy-MM-dd');
  form.is_paid = true;
  isAddEditModalOpen.value = true;
};

const openEditModal = (t: any) => {
  form.id = t.id;
  form.description = t.description;
  form.value = t.value;
  valueInput.value = String(t.value);
  form.date = t.date;
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
  isAddEditModalOpen.value = true;
};

const closeModal = () => { isAddEditModalOpen.value = false; };

const submit = () => {
  const options = { onSuccess: () => closeModal(), preserveScroll: true };
  if (form.id) form.put(route('transactions.update', form.id), options);
  else form.post(route('transactions.store'), options);
};

function prettyDate(dateStr: string): string {
  if (!dateStr) return '—';
  try { const iso = parseISO(dateStr as unknown as string); if (isValidDate(iso)) return format(iso, 'dd MMM yyyy'); } catch {}
  try { const d = parse(dateStr as unknown as string, 'yyyy-MM-dd', new Date()); if (isValidDate(d)) return format(d, 'dd MMM yyyy'); } catch {}
  return String(dateStr);
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Transactions" />

    <!-- Toolbar -->
    <div class="flex w-full items-center justify-between p-4">
      <h1 class="text-2xl font-semibold">Transactions</h1>
      <div class="flex gap-2">
        <Button @click="openAddModal('expense')" variant="destructive">
          <Icon name="plus" class="mr-2 h-4 w-4" />
          New Expense
        </Button>
        <Button @click="openAddModal('income')" class="bg-emerald-600 hover:bg-emerald-700">
          <Icon name="plus" class="mr-2 h-4 w-4" />
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
            <template v-if="transactions.data.length > 0">
              <TableRow v-for="transaction in transactions.data" :key="transaction.id" class="group hover:bg-black/5 dark:hover:bg-white/5">
                <TableCell class="font-mono text-[13px] opacity-80">{{ prettyDate(transaction.date as unknown as string) }}</TableCell>

                <TableCell>
                  <div class="flex items-center gap-2">
                    <span class="font-medium">{{ transaction.description }}</span>
                    <span v-if="transaction.is_paid" class="hidden rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-400 sm:inline">paid</span>
                    <span v-else class="hidden rounded-full bg-amber-500/10 px-2 py-0.5 text-[11px] font-medium text-amber-400 sm:inline">pending</span>
                  </div>
                </TableCell>

                <TableCell>
                  <div v-if="transaction.category" class="inline-flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full" :style="{ backgroundColor: transaction.category.color }">
                      <Icon :name="transaction.category.icon" class="h-4 w-4 text-white" />
                    </span>
                    <span>{{ transaction.category.name }}</span>
                  </div>
                </TableCell>

                <TableCell class="text-sm opacity-90">{{ transaction.account.name }}</TableCell>

                <TableCell class="text-right font-mono">
                  <span :class="transaction.type === 'income' ? 'text-emerald-400' : 'text-rose-400'">
                    {{ transaction.type === 'income' ? '+' : '-' }} {{ formatCurrency(transaction.value, user.currency) }}
                  </span>
                </TableCell>

                <TableCell class="text-right">
                  <div class="flex justify-end gap-1 opacity-60 transition group-hover:opacity-100">
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditModal(transaction)">
                      <Icon name="pencil" class="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-rose-400 hover:text-rose-300">
                      <Icon name="trash-2" class="h-4 w-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </template>
            <template v-else>
              <TableRow>
                <TableCell colspan="6" class="py-14">
                  <div class="flex flex-col items-center gap-3 text-center text-gray-500 dark:text-gray-400">
                    <Icon name="inbox" class="h-6 w-6 opacity-60" />
                    <div class="text-sm">No transactions found.</div>
                    <Button class="mt-2 bg-emerald-600 hover:bg-emerald-700"><Icon name="plus" class="mr-2 h-4 w-4"/>Add your first</Button>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>
    </div>

    <!-- Modal -->
         <Dialog :open="isAddEditModalOpen" @update:open="isAddEditModalOpen = $event">
      <DialogContent :class="['w-full transition-all duration-300', showMore ? 'sm:max-w-4xl' : 'sm:max-w-xl']" @close-auto-focus="closeModal">
        <DialogHeader class="border-b border-white/10 pb-3">
          <DialogTitle class="flex items-center gap-3">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl" :class="form.type === 'expense' ? 'bg-rose-500/15 text-rose-400' : 'bg-emerald-500/15 text-emerald-400'">
              <Icon :name="form.type === 'expense' ? 'arrow-down-circle' : 'arrow-up-circle'" class="h-5 w-5" />
            </span>
            {{ form.id ? 'Edit Transaction' : form.type === 'expense' ? 'New Expense' : 'New Income' }}
          </DialogTitle>
        </DialogHeader>

        <!-- Form -->
        <form @submit.prevent="submit" :class="['gap-6 pt-2 transition-all duration-300', showMore ? 'grid grid-cols-1 md:grid-cols-2' : 'grid grid-cols-1']">
          <!-- MAIN COLUMN -->
          <div class="space-y-5">
            <!-- Value + Paid -->
            <div class="flex items-end gap-4">
              <div class="flex-1">
                <Label class="text-sm text-white/70" for="value">Value</Label>
                <div class="relative mt-1">
                  <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/40">{{ user.currency || '€' }}</span>
                  <Input id="value" v-model="valueInput" type="text" inputmode="decimal" placeholder="0.00"
                         class="h-14 rounded-2xl border-white/10 bg-white/5 pl-9 text-2xl font-semibold tracking-tight"
                         required @input="(e: Event) => formatCurrencyInput(e, (val) => (valueInput = val), user.currency)" />
                  <div class="absolute inset-0 rounded-2xl ring-0 transition focus-within:ring-2 focus-within:ring-white/15"></div>
                </div>
                <InputError :message="form.errors.value" class="mt-1" />
              </div>

              <div class="pb-1">
                <Label class="text-sm text-white/70" for="is_paid">{{ form.type === 'expense' ? 'Paid' : 'Received' }}</Label>
                <div class="mt-2 flex items-center gap-2">
                  <button type="button"
                          :class="['inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm transition',
                                   form.is_paid ? 'border-emerald-400/30 bg-emerald-500/15 text-emerald-300' : 'border-white/10 bg-white/5 text-white/70']"
                          @click="form.is_paid = !form.is_paid">
                    <span class="h-2.5 w-2.5 rounded-full" :class="form.is_paid ? 'bg-emerald-400' : 'bg-white/30'"></span>
                    {{ form.is_paid ? 'Yes' : 'No' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Date -->
            <div>
              <Label class="text-sm text-white/70" for="date">Date</Label>
              <div class="mt-2 flex flex-wrap items-center gap-2">
                <div class="inline-flex overflow-hidden rounded-xl border border-white/10">
                  <Button type="button" size="sm" :variant="selectedDatePreset === 'today' ? 'default' : 'ghost'" class="rounded-none" @click="setDatePreset('today')">Today</Button>
                  <Button type="button" size="sm" :variant="selectedDatePreset === 'yesterday' ? 'default' : 'ghost'" class="rounded-none" @click="setDatePreset('yesterday')">Yesterday</Button>
                </div>
                <div class="relative">
                  <Input id="date" v-model="form.date" type="date" class="h-10 rounded-xl border-white/10 bg-white/5 pr-9" required />
                  <Icon name="calendar" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 opacity-50" />
                </div>
              </div>
              <InputError :message="form.errors.date" class="mt-1" />
            </div>

            <!-- Description -->
            <div>
              <Label class="text-sm text-white/70" for="description">Description</Label>
              <Input id="description" v-model="form.description" class="mt-2 h-10 rounded-xl border-white/10 bg-white/5" required />
              <InputError :message="form.errors.description" class="mt-1" />
            </div>

            <!-- Account & Category -->
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <Label class="text-sm text-white/70" for="account_id">Account</Label>
                <Select v-model="form.account_id">
                  <SelectTrigger class="mt-2 h-10 rounded-xl border-white/10 bg-white/5">
                    <SelectValue placeholder="Select an account" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectItem v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.account_id" class="mt-1" />
              </div>

              <div>
                <Label class="text-sm text-white/70" for="category_id">Category</Label>
                <Select v-model="form.category_id">
                  <SelectTrigger class="mt-2 h-10 rounded-xl border-white/10 bg-white/5">
                    <SelectValue placeholder="Select a category" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectItem v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.category_id" class="mt-1" />
              </div>
            </div>
          </div>

          <!-- SIDEBAR: More details -->
          <Transition name="slide-fade">
            <div v-if="showMore" class="space-y-4 pt-6 md:border-l md:pt-0 md:pl-6">
              <div>
                <Label class="text-sm text-white/70" for="tag_id">Tag (Optional)</Label>
                <Select v-model="form.tag_id">
                  <SelectTrigger class="mt-2 h-10 rounded-xl border-white/10 bg-white/5">
                    <SelectValue placeholder="Select a tag" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectItem :value="null">None</SelectItem>
                      <SelectItem v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.name }}</SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.tag_id" class="mt-1" />
              </div>

              <div>
                <Label class="text-sm text-white/70" for="notes">Notes (Optional)</Label>
                <Textarea id="notes" v-model="form.notes" class="mt-2 min-h-[90px] rounded-xl border-white/10 bg-white/5" />
                <InputError :message="form.errors.notes" class="mt-1" />
              </div>

              <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between">
                  <span class="flex items-center gap-2">
                    <Icon name="pin" class="h-5 w-5 text-gray-500" />
                    <Label class="text-sm text-white/70" for="is_fixed">Fixed income/expense</Label>
                  </span>
                  <Switch id="is_fixed" v-model="form.is_fixed" :disabled="form.is_recurring" />
                </div>
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2">
                      <Icon name="repeat" class="h-5 w-5 text-gray-500" />
                      <Label class="text-sm text-white/70" for="is_recurring">Repeat transaction</Label>
                    </span>
                    <Switch id="is_recurring" v-model="form.is_recurring" :disabled="form.is_fixed" />
                  </div>
                  <Transition name="slide-fade">
                    <div v-if="form.is_recurring" class="mt-4 flex items-center gap-2">
                      <Input id="installments" v-model="form.installments" type="number" min="1" class="w-24 rounded-xl border-white/10 bg-white/5" />
                      <span class="text-sm text-gray-500 dark:text-gray-400">times</span>
                      <Select v-model="form.installment_period">
                        <SelectTrigger class="h-10 rounded-xl border-white/10 bg-white/5">
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

          <!-- FOOTER -->
          <div class="mt-6 flex items-center justify-between md:col-span-2">
            <Button type="button" variant="ghost" size="sm" @click="showMore = !showMore">
              <Icon :name="showMore ? 'chevron-up' : 'chevron-down'" class="mr-1 h-4 w-4" />
              {{ showMore ? 'Less details' : 'More details' }}
            </Button>

            <DialogFooter class="gap-2">
              <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
              <Button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700">
                {{ form.processing ? 'Saving...' : 'Save' }}
              </Button>
            </DialogFooter>
          </div>
        </form>
      </DialogContent>
    </Dialog>

  </AppLayout>
</template>

<style>
.slide-fade-enter-active,
.slide-fade-leave-active { transition: all 0.25s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to { opacity: 0; transform: translateX(16px); }

.dark input[type='date']::-webkit-calendar-picker-indicator { filter: invert(1); }
</style>
