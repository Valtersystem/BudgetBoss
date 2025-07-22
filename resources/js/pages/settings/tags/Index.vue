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

type Tag = {
    id: number;
    name: string;
};

const props = defineProps<{
    tags: Array<Tag>
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings' },
    { title: 'Tags', href: '/settings/tags' },
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

const openUpdateModal = (tag: Tag) => {
    isUpdate.value = true;
    form.id = tag.id;
    form.name = tag.name;
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
        form.put(route('tags.update', form.id), options);
    } else {
        form.post(route('tags.store'), options);
    }
};

const destroy = (id: number) => {
    if (confirm('Are you sure you want to delete this tag?')) {
        router.delete(route('tags.destroy', id));
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Tag Settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium">Tags</h3>
                        <p class="text-sm text-muted-foreground">
                            Manage your transaction tags.
                        </p>
                    </div>
                    <Button @click="openCreateModal">New Tag</Button>
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
                            <tr v-if="tags.length === 0">
                                <td colspan="2" class="p-4 text-center text-muted-foreground">
                                    No tags found.
                                </td>
                            </tr>
                            <tr v-for="tag in tags" :key="tag.id" class="border-b last:border-b-0">
                                <td class="p-4">{{ tag.name }}</td>
                                <td class="p-4">
                                    <div class="flex items-center justify-end space-x-2">
                                        <Button @click="openUpdateModal(tag)" variant="ghost" size="icon" class="h-8 w-8">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </Button>
                                         <Button @click="destroy(tag.id)" variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive">
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
                        <DialogTitle>{{ isUpdate ? 'Edit Tag' : 'Register New Tag' }}</DialogTitle>
                        <DialogDescription>
                            Tags help you group transactions.
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
                            {{ isUpdate ? 'Update Tag' : 'Save Tag' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>
