<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Circle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    categories: {
        type: Array as () => App.Models.Category[],
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
        title: 'Categories',
        href: '/settings/categories',
    },
];

// Estado para a pesquisa e para o tipo de categoria selecionado
const searchTerm = ref('');
const selectedType = ref<'expense' | 'income'>('expense');

// Classe do botão da dropdown com base no tipo selecionado
const dropdownTriggerClass = computed(() => ({
    'bg-red-500 hover:bg-red-600 text-white': selectedType.value === 'expense',
    'bg-green-500 hover:bg-green-600 text-white': selectedType.value === 'income',
}));

// Filtra as categorias com base no tipo e no termo de pesquisa
const filteredCategories = computed(() => {
    return props.categories.filter((category) => {
        const typeMatch = category.type === selectedType.value;
        const searchMatch = category.name.toLowerCase().includes(searchTerm.value.toLowerCase());
        return typeMatch && searchMatch;
    });
});

// Listas de cores e ícones predefinidos
const colors = [
    '#111827',
    '#6B7280',
    '#D1D5DB',
    '#F9FAFB',
    '#9D174D',
    '#EC4899',
    '#F472B6',
    '#FCE7F3',
    '#5B21B6',
    '#8B5CF6',
    '#A78BFA',
    '#EDE9FE',
    '#1E3A8A',
    '#3B82F6',
    '#60A5FA',
    '#DBEAFE',
    '#166534',
    '#22C55E',
    '#86EFAC',
    '#DCFCE7',
    '#CA8A04',
    '#EAB308',
    '#FACC15',
    '#FEF9C3',
    '#C2410C',
    '#F97316',
    '#FDBA74',
    '#FFEDD5',
    '#B91C1C',
    '#EF4444',
    '#FCA5A5',
    '#FEE2E2',
];
// Lista de ícones reativa para permitir a reordenação
const icons = ref([
    'wallet',
    'piggy-bank',
    'landmark',
    'credit-card',
    'dollar-sign',
    'coins',
    'bar-chart-2',
    'pie-chart',
    'receipt',
    'banknote',
    'home',
    'car',
    'bus',
    'train-front',
    'bike',
    'plane',
    'ship',
    'truck',
    'utensils',
    'pizza',
    'beer',
    'coffee',
    'music',
    'film',
    'gamepad-2',
    'book',
    'heart',
    'activity',
    'pill',
    'dumbbell',
    'stethoscope',
    'briefcase',
    'graduation-cap',
    'clipboard',
    'notebook-text',
    'laptop',
    'gift',
    'shopping-cart',
    'shirt',
    'baby',
    'dog',
    'cat',
    'flower-2',
    'globe',
    'map',
    'sun',
    'moon',
    'cloud',
    'star',
]);

const primaryColors = computed(() => colors.slice(0, 5));
const otherColors = computed(() => colors.slice(5));
const primaryIcons = computed(() => icons.value.slice(0, 5));
const otherIcons = computed(() => icons.value.slice(5));

const form = useForm({
    id: null as number | null,
    name: '',
    icon: icons.value[0],
    color: colors[0],
    type: selectedType.value,
});

// Estado para controlar a visibilidade do modal
const isAddEditModalOpen = ref(false);

/**
 * Seleciona um ícone, atualiza o formulário e reordena a lista de ícones
 * para que o ícone selecionado apareça em primeiro lugar.
 * @param {string} iconName - O nome do ícone a ser selecionado.
 */
const selectIcon = (iconName: string) => {
    form.icon = iconName;
    const index = icons.value.indexOf(iconName);
    if (index > -1) {
        // Remove o ícone da sua posição atual
        const [selectedIcon] = icons.value.splice(index, 1);
        // Adiciona o ícone no início da lista
        icons.value.unshift(selectedIcon);
    }
};

// Abre o modal para adicionar uma nova categoria
const openAddModal = () => {
    form.reset();
    form.type = selectedType.value; // Garante que o tipo está correto
    isAddEditModalOpen.value = true;
};

// Abre o modal para editar uma categoria existente
const openEditModal = (category: App.Models.Category) => {
    form.id = category.id;
    form.name = category.name;
    form.icon = category.icon;
    form.color = category.color;
    form.type = category.type;
    // Reordena a lista de ícones para que o da categoria atual apareça primeiro
    selectIcon(category.icon);
    isAddEditModalOpen.value = true;
};

// Fecha o modal e re-inicializa o formulário
const closeModal = () => {
    isAddEditModalOpen.value = false;
    form.reset();
    router.reload({ only: ['categories'] });
};

// Submete o formulário de criação/edição
const submit = () => {
    if (form.id) {
        // Atualiza a categoria existente
        form.put(route('categories.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        // Cria uma nova categoria
        form.post(route('categories.store'), {
            onSuccess: () => closeModal(),
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Categories" />

        <div class="flex w-full justify-between p-4">
            <!-- Cabeçalho da página -->
            <div class="mb-6 flex items-center justify-between">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button :class="dropdownTriggerClass" class="flex items-center gap-2">
                            {{ selectedType === 'expense' ? 'Expenses Categories' : 'Incomes Categories' }}
                            <Icon name="chevron-down" class="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuItem @click="selectedType = 'expense'"> Expenses Categories </DropdownMenuItem>
                        <DropdownMenuItem @click="selectedType = 'income'"> Incomes Categories </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <div class="flex gap-4">
                <div class="">
                    <Input v-model="searchTerm" placeholder="Search categories..." class="max-w-sm" />
                </div>
                <Button @click="openAddModal" :class="dropdownTriggerClass" class="flex h-10 w-10 items-center justify-center rounded-full">
                    <Icon name="plus" class="text-white" />
                </Button>
            </div>
        </div>

        <div class="w-full p-4">
            <!-- Tabela de categorias -->
            <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
                <Table>
                    <TableHeader class="bg-gray-50 text-white dark:bg-[#3A3A3C]">
                        <TableRow class="text-red-700 dark:text-red-200">
                            <TableHead class="w-1/4 dark:text-white">Icon</TableHead>
                            <TableHead class="dark:text-white">Name</TableHead>
                            <TableHead class="dark:text-white">Color</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="filteredCategories.length > 0">
                            <TableRow v-for="category in filteredCategories" :key="category.id">
                                <TableCell>
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full"
                                        :style="{ backgroundColor: category.color }"
                                    >
                                        <Icon :name="category.icon" class="h-5 w-5 text-white" />
                                    </span>
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ category.name }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    <Circle :style="{ backgroundColor: category.color, color: category.color }" class="rounded-[100%]" />
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="outline" size="sm" class="mr-2" @click="openEditModal(category)">
                                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                                        Edit
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="3" class="py-8 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">No categories found for "{{ selectedType }}" type.</p>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Modal para Adicionar/Editar Categoria -->
        <Dialog :open="isAddEditModalOpen" @update:open="isAddEditModalOpen = $event">
            <DialogContent class="sm:max-w-md" @close-auto-focus="closeModal">
                <DialogHeader>
                    <DialogTitle>{{ form.id ? 'Edit Category' : 'Register new category' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit">
                    <div class="grid gap-6 py-4">
                        <div>
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" class="mt-1 w-full" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <Label>Category color</Label>
                            <div class="mt-2 flex items-center gap-2">
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
                            <InputError class="mt-2" :message="form.errors.color" />
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
