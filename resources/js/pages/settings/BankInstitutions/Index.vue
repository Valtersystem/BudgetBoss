<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { icons as bankIcons } from '@/lib/icons-and-colors.ts';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    bankInstitutions: {
        type: Array as () => App.Models.BankInstitution[],
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
        title: 'Bank Institutions',
        href: '/settings/bank-institutions',
    },
];

// Estado para a pesquisa
const searchTerm = ref('');

// Filtra as instituições com base no termo de pesquisa
const filteredBankInstitutions = computed(() => {
    return props.bankInstitutions.filter((institution) => {
        return institution.name.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
});

// Cria uma cópia reativa da lista de ícones para permitir a reordenação
const icons = ref([...bankIcons]);

const primaryIcons = computed(() => icons.value.slice(0, 5));
const otherIcons = computed(() => icons.value.slice(5));

const form = useForm({
    id: null as number | null,
    name: '',
    icon: icons.value[0],
});

// Estado para controlar a visibilidade do modal
const isAddEditModalOpen = ref(false);

/**
 * Seleciona um ícone, atualiza o formulário e reordena a lista de ícones
 * para que o ícone selecionado apareça em primeiro lugar.
 */
const selectIcon = (iconName: string) => {
    form.icon = iconName;
    const index = icons.value.indexOf(iconName);
    if (index > -1) {
        const [selectedIcon] = icons.value.splice(index, 1);
        icons.value.unshift(selectedIcon);
    }
};

// Abre o modal para adicionar uma nova instituição
const openAddModal = () => {
    form.reset();
    isAddEditModalOpen.value = true;
};

// Abre o modal para editar uma instituição existente
const openEditModal = (institution: App.Models.BankInstitution) => {
    form.id = institution.id;
    form.name = institution.name;
    form.icon = institution.icon ?? '';
    selectIcon(form.icon); // Reordena os ícones
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
        // Usa o método PUT para atualizar
        form.put(route('bank-institutions.update', { bank_institution: form.id }), {
            onSuccess: () => closeModal(),
        });
    } else {
        // Usa o método POST para criar
        form.post(route('bank-institutions.store'), {
            onSuccess: () => closeModal(),
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Bank Institutions" />

        <div class="flex w-full justify-between p-4">
            <!-- Cabeçalho da página -->
            <div class="flex items-center">
                <h1 class="text-2xl font-semibold">Bank Institutions</h1>
            </div>

            <div class="flex items-center gap-4">
                <Input v-model="searchTerm" placeholder="Search institutions..." class="max-w-sm" />
                <Button @click="openAddModal" class="flex h-10 w-10 items-center justify-center rounded-full">
                    <Icon name="plus" />
                </Button>
            </div>
        </div>

        <div class="w-full p-4">
            <!-- Tabela de instituições -->
            <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
                <Table>
                    <TableHeader class="bg-gray-50 text-white dark:bg-[#3A3A3C]">
                        <TableRow>
                            <TableHead class="w-20 dark:text-white">Icon</TableHead>
                            <TableHead class="dark:text-white">Name</TableHead>
                            <TableHead class="text-right dark:text-white">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="filteredBankInstitutions.length > 0">
                            <TableRow v-for="institution in filteredBankInstitutions" :key="institution.id">
                                <TableCell>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 dark:bg-white">
                                        <Icon :name="institution.icon ?? 'landmark'" class="h-5 w-5 text-gray-500" />
                                    </span>
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ institution.name }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="outline" size="sm" class="mr-2" @click="openEditModal(institution)">
                                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                                        Edit
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="3" class="py-8 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">No bank institutions found.</p>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Modal para Adicionar/Editar Instituição Bancária -->
        <Dialog :open="isAddEditModalOpen" @update:open="isAddEditModalOpen = $event">
            <DialogContent class="sm:max-w-md" @close-auto-focus="closeModal">
                <DialogHeader>
                    <DialogTitle>{{ form.id ? 'Edit Bank Institution' : 'Register New Bank Institution' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit">
                    <div class="grid gap-6 py-4">
                        <div>
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" class="mt-1 w-full" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <Label>Icon</Label>
                            <div class="mt-2 flex items-center gap-2">
                                <button
                                    v-for="icon in primaryIcons"
                                    :key="icon"
                                    type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-lg transition"
                                    :class="{
                                        'bg-gray-200 dark:bg-gray-700': form.icon === icon,
                                        'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700': form.icon !== icon,
                                    }"
                                    @click="selectIcon(icon)"
                                >
                                    <Icon :name="icon" class="h-6 w-6" />
                                </button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button type="button" variant="outline" size="sm">Others</Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent class="p-2">
                                        <div class="grid grid-cols-6 gap-1">
                                            <button
                                                v-for="icon in otherIcons"
                                                :key="icon"
                                                type="button"
                                                class="flex h-10 w-10 items-center justify-center rounded-lg transition"
                                                :class="{
                                                    'bg-gray-200 dark:bg-gray-700': form.icon === icon,
                                                    'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700': form.icon !== icon,
                                                }"
                                                @click="selectIcon(icon)"
                                            >
                                                <Icon :name="icon" class="h-6 w-6" />
                                            </button>
                                        </div>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <InputError class="mt-2" :message="form.errors.icon" />
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
