<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    tags: {
        type: Array as () => App.Models.Tag[],
        required: true,
    },
});

// Breadcrumbs para navegação
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/settings/profile',
    },
    {
        title: 'Tags',
        href: '/settings/tags',
    },
];

// Estado para a pesquisa
const searchTerm = ref('');

// Filtra as tags com base no termo de pesquisa
const filteredTags = computed(() => {
    return props.tags.filter((tag) => {
        return tag.name.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
});

const form = useForm({
    id: null as number | null,
    name: '',
});

// Estado para controlar a visibilidade do modal
const isAddEditModalOpen = ref(false);

// Abre o modal para adicionar uma nova tag
const openAddModal = () => {
    form.reset();
    isAddEditModalOpen.value = true;
};

// Abre o modal para editar uma tag existente
const openEditModal = (tag: App.Models.Tag) => {
    form.id = tag.id;
    form.name = tag.name;
    isAddEditModalOpen.value = true;
};

// Fecha o modal e re-inicializa o formulário
const closeModal = () => {
    isAddEditModalOpen.value = false;
    form.reset();
};

// Submete o formulário de criação/edição
const submit = () => {
    if (form.id) {
        // Atualiza a tag existente
        form.put(route('tags.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        // Cria uma nova tag
        form.post(route('tags.store'), {
            onSuccess: () => closeModal(),
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Tags" />

        <div class="flex w-full justify-between p-4">
            <!-- Cabeçalho da página -->
            <div class="flex items-center">
                <h1 class="text-2xl font-semibold">Tags</h1>
            </div>

            <div class="flex items-center gap-4">
                <Input v-model="searchTerm" placeholder="Search tags..." class="max-w-sm" />
                <Button @click="openAddModal" class="flex h-10 w-10 items-center justify-center rounded-full">
                    <Icon name="plus" />
                </Button>
            </div>
        </div>

        <div class="w-full p-4">
            <!-- Tabela de tags -->
            <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
                <Table>
                    <TableHeader class="bg-gray-50 text-white dark:bg-[#3A3A3C]">
                        <TableRow>
                            <TableHead class="dark:text-white">Name</TableHead>
                            <TableHead class="text-right dark:text-white">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="filteredTags.length > 0">
                            <TableRow v-for="tag in filteredTags" :key="tag.id">
                                <TableCell class="font-medium">
                                    {{ tag.name }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="outline" size="sm" class="mr-2" @click="openEditModal(tag)">
                                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                                        Edit
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="2" class="py-8 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">No tags found.</p>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Modal para Adicionar/Editar Tag -->
        <Dialog :open="isAddEditModalOpen" @update:open="isAddEditModalOpen = $event">
            <DialogContent class="sm:max-w-md" @close-auto-focus="closeModal">
                <DialogHeader>
                    <DialogTitle>{{ form.id ? 'Edit Tag' : 'Register new tag' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit">
                    <div class="grid gap-6 py-4">
                        <div>
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" class="mt-1 w-full" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
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
