<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';

type AccountType = {
    id: number;
    name: string;
};

const props = defineProps<{
    account_types: Array<AccountType>
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings' },
    { title: 'Account Types', href: '/settings/account-types' },
];

const isModalOpen = ref(false);
const isUpdate = ref(false);

const form = useForm({
    id: null as number | null,
    name: '',
});

const openCreateModal = () => {
    isUpdate.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openUpdateModal = (accountType: AccountType) => {
    isUpdate.value = true;
    form.id = accountType.id;
    form.name = accountType.name;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    const options = {
        onSuccess: () => closeModal(),
    };
    if (isUpdate.value) {
        form.put(route('account-types.update', form.id), options);
    } else {
        form.post(route('account-types.store'), options);
    }
};

const destroy = (id: number) => {
    if (confirm('Are you sure you want to delete this account type?')) {
        router.delete(route('account-types.destroy', id));
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Account Type Settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium">Account Types</h3>
                        <p class="text-sm text-muted-foreground">
                            Manage your account types (e.g., Checking, Savings).
                        </p>
                    </div>
                    <Button @click="openCreateModal">New Type</Button>
                </div>

                <div class="border rounded-md">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr class="text-left align-middle">
                                <th class="p-4 font-medium">Name</th>
                                <th class="p-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="account_types.length === 0">
                                <td colspan="2" class="p-4 text-center text-muted-foreground">
                                    No account types found.
                                </td>
                            </tr>
                            <tr v-for="accountType in account_types" :key="accountType.id" class="border-b last:border-b-0">
                                <td class="p-4">{{ accountType.name }}</td>
                                <td class="p-4">
                                    <div class="flex items-center justify-end space-x-2">
                                        <Button @click="openUpdateModal(accountType)" variant="ghost" size="icon" class="h-8 w-8">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </Button>
                                         <Button @click="destroy(accountType.id)" variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </SettingsLayout>

        <Dialog :open="isModalOpen" @update:open="closeModal">
            <DialogContent>
                 <form @submit.prevent="submit">
                    <DialogHeader>
                        <DialogTitle>{{ isUpdate ? 'Edit Account Type' : 'Register New Account Type' }}</DialogTitle>
                        <DialogDescription>
                            Add a new type of account.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="grid gap-4 py-6">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required />
                            <InputError :message="form.errors.name" />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing">
                            {{ isUpdate ? 'Update Type' : 'Save Type' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>
