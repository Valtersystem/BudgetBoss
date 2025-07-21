<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import CategoryForm from './partials/CategoryForm.vue';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';

type Category = {
    id: number;
    name: string;
    type: 'income' | 'expense';
    icon: string | null;
    color: string | null;
};

const props = defineProps<{
    categories: Array<Category>
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings' },
    { title: 'Categories', href: '/settings/categories' },
];

// --- Lógica para o Modal e Formulário ---
const isModalOpen = ref(false);
const isUpdate = ref(false);

const form = useForm({
    id: null as number | null,
    name: '',
    type: 'expense' as 'income' | 'expense',
    color: '#808080',
});

const openCreateModal = () => {
    isUpdate.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openUpdateModal = (category: Category) => {
    isUpdate.value = true;
    form.id = category.id;
    form.name = category.name;
    form.type = category.type;
    form.color = category.color || '#808080';
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
        form.put(route('settings.categories.update', form.id), options);
    } else {
        form.post(route('settings.categories.store'), options);
    }
};

const destroy = (id: number) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('settings.categories.destroy', id));
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Category Settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium">Categories</h3>
                        <p class="text-sm text-muted-foreground">
                            Manage your expense and income categories.
                        </p>
                    </div>
                    <Button @click="openCreateModal">New Category</Button>
                </div>

                <div class="border rounded-md">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr class="text-left align-middle">
                                <th class="p-4 font-medium">Name</th>
                                <th class="p-4 font-medium">Type</th>
                                <th class="p-4 font-medium">Color</th>
                                <th class="p-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="categories.length === 0">
                                <td colspan="5" class="p-4 text-center text-muted-foreground">
                                    No categories found.
                                </td>
                            </tr>
                            <tr v-for="category in categories" :key="category.id" class="border-b last:border-b-0">
                                <td class="p-4">{{ category.name }}</td>
                                <td class="p-4 capitalize">{{ category.type }}</td>
                                <td class="p-4">
                                    <div v-if="category.color" class="h-5 w-5 rounded-full"
                                        :style="{ backgroundColor: category.color }"></div>
                                </td>
                                <td class="p-4">
                                    <Icon v-if="category.icon" :name="category.icon" class="text-muted-foreground" />
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-end space-x-2">
                                        <Button @click="openUpdateModal(category)" variant="ghost" size="icon"
                                            class="h-8 w-8">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                        </Button>
                                        <Button @click="destroy(category.id)" variant="ghost" size="icon"
                                            class="h-8 w-8 text-destructive hover:text-destructive">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18" />
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                <line x1="10" x2="10" y1="11" y2="17" />
                                                <line x1="14" x2="14" y1="11" y2="17" />
                                            </svg>
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
                <CategoryForm :form="form" :is-update="isUpdate" @submit="submit" />
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>
