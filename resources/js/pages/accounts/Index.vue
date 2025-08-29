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
    import { Textarea } from '@/components/ui/textarea';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { formatCurrency, formatCurrencyInput } from '@/lib/currency';
    import { colors } from '@/lib/icons-and-colors';
    import { type BreadcrumbItem } from '@/types';
    import { Head, useForm, usePage } from '@inertiajs/vue3';
    import { computed, ref, watch } from 'vue';

    const props = defineProps({
        accounts: {
            type: Array as () => App.Models.Account[],
            required: true,
        },
        bankInstitutions: {
            type: Array as () => App.Models.BankInstitution[],
            required: true,
        },
    });

    const user = usePage().props.auth.user as unknown as App.Models.User;

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Accounts',
            href: '/accounts',
        },
    ];

    const form = useForm({
        id: null as number | null,
        name: '',
        bank_institution_id: null as number | null,
        initial_balance: 0,
        source_of_money: '',
        description: '',
        color: colors[0],
        dashboard: Boolean(true),
    });

    // Computed property para o saldo total no dashboard
    const dashboardBalance = computed(() => {
        return props.accounts
            .filter(account => account.dashboard)
            .reduce((sum, account) => sum + account.initial_balance, 0);
    });

    // Computed property para o saldo total de todas as contas
    const totalBalance = computed(() => {
        return props.accounts.reduce((sum, account) => sum + account.initial_balance, 0);
    });

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

    watch(balanceInput, (newValue) => {
        const rawDigits = newValue.replace(/\D/g, '');
        const numberValue = Number(rawDigits);

        if (!isNaN(numberValue)) {
            form.initial_balance = numberValue / 100;
        } else {
            form.initial_balance = 0;
        }
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
            form.put(route('accounts.update', { account: form.id }), {
                onSuccess: () => closeModal(),
            });
        } else {
            form.post(route('accounts.store'), {
                onSuccess: () => closeModal(),
            });
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

            <!-- Seção de Saldos Totais -->
             <div>
            <p>{{ form.dashboard }}</p>
             </div>
            <div class="grid grid-cols-1 gap-6 p-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Current balance</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(dashboardBalance, user.currency) }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                            <Icon name="dollar-sign" />
                        </div>
                    </div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Balance</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(totalBalance, user.currency) }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300">
                            <Icon name="scale" />
                        </div>
                    </div>
                </div>
            </div>


            <div class="grid grid-cols-1 gap-6 p-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="account in props.accounts"
                    :key="account.id"
                    class="flex flex-col justify-between overflow-hidden rounded-lg border bg-white shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                    :style="{ borderLeft: `4px solid ${account.color}` }"
                >
                    <div class="p-4">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                <Icon :name="account.bank_institution.icon ?? 'landmark'" class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                            </span>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ account.bank_institution.name }}</p>
                                <h3 class="font-semibold">{{ account.name }}</h3>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Current Balance</p>
                            <p class="text-2xl font-bold">
                                {{ formatCurrency(account.initial_balance, user.currency) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end border-t bg-gray-50 p-2 dark:border-gray-800 dark:bg-gray-950">
                        <Button variant="ghost" size="sm" @click="openEditModal(account)">
                            <Icon name="pencil" class="mr-1 h-4 w-4" />
                            Edit
                        </Button>
                    </div>
                </div>
            </div>

            <Dialog :open="isAddEditModalOpen" @update:open="isAddEditModalOpen = $event">
                <DialogContent class="sm:max-w-lg" @close-auto-focus="closeModal">
                    <DialogHeader>
                        <DialogTitle>{{ form.id ? 'Edit Account' : 'Register New Account' }}</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-4 py-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <Label for="initial_balance">Initial Balance</Label>
                                <Input
                                    id="initial_balance"
                                    v-model="balanceInput"
                                    type="text"
                                    inputmode="decimal"
                                    class="mt-1 text-left dark:text-2xl"
                                    placeholder="0.00"
                                    @input="(e: Event) => formatCurrencyInput(e, (val) => (balanceInput = val), user.currency)"
                                />
                                <InputError :message="form.errors.initial_balance" class="mt-1" />
                            </div>

                            <div class="md:col-span-2">
                                <Label for="name">Account Name</Label>
                                <Input id="name" v-model="form.name" class="mt-1 capitalize" />
                                <InputError :message="form.errors.name" class="mt-1" />
                            </div>

                            <div class="md:col-span-2">
                                <Label for="bank_institution_id">Bank Institution</Label>
                                <Select v-model="form.bank_institution_id">
                                    <SelectTrigger class="mt-1 capitalize">
                                        <SelectValue placeholder="Select an institution" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="institution in bankInstitutions" :key="institution.id" :value="institution.id" class="capitalize">
                                                {{ institution.name }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.bank_institution_id" class="mt-1" />
                            </div>

                            <div class="md:col-span-2">
                                <Label for="source_of_money">Source of Money</Label>
                                <Select v-model="form.source_of_money">
                                    <SelectTrigger class="mt-1">
                                        <SelectValue placeholder="Select a source" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="option in sourceOfMoneyOptions" :key="option.value" :value="option.value">
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

                            <div class="md:col-span-2">
                                <Label for="description">Description (Optional)</Label>
                                <Textarea id="description" v-model="form.description" class="mt-1" />
                                <InputError :message="form.errors.description" class="mt-1" />
                            </div>

                            <div class="md:col-span-2">
                                <Label>Color</Label>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <button
                                        v-for="color in primaryColors"
                                        :key="color"
                                        type="button"
                                        class="h-8 w-8 transform rounded-full transition-transform hover:scale-110"
                                        :style="{ backgroundColor: color }"
                                        @click="form.color = color"
                                    >
                                        <Icon v-if="form.color === color" name="check" class="mx-auto h-5 w-5 text-white" />
                                    </button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button type="button" variant="outline" size="sm">Others</Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent class="p-2">
                                            <div class="grid grid-cols-6 gap-1">
                                                <button
                                                    v-for="color in otherColors"
                                                    :key="color"
                                                    type="button"
                                                    class="h-8 w-8 transform rounded-full transition-transform hover:scale-110"
                                                    :style="{ backgroundColor: color }"
                                                    @click="form.color = color"
                                                >
                                                    <Icon v-if="form.color === color" name="check" class="mx-auto h-5 w-5 text-white" />
                                                </button>
                                            </div>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                                <InputError :message="form.errors.color" class="mt-1" />
                            </div>

                            <!-- Novo campo Switch -->
                            <div class="md:col-span-2 flex items-center justify-between mt-4">
                                <Label for="dashboard-switch">Include sum on dashboard</Label>
                                <Switch id="dashboard-switch" v-model:checked="form.dashboard" />
                            </div>
                            <InputError :message="form.errors.dashboard" class="mt-1 md:col-span-2" />

                        </div>
                        <DialogFooter class="mt-6">
                            <DialogClose as-child>
                                <Button type="button" variant="secondary" @click="closeModal"> Cancel </Button>
                            </DialogClose>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    </template>
