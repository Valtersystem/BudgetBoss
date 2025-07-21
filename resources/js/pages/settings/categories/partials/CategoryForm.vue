<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import InputError from '@/components/InputError.vue';
import Icon from '@/components/Icon.vue';

// Este componente recebe o formulário do Inertia como uma "prop"
const props = defineProps<{
    form: {
        name: string;
        type: 'income' | 'expense';
        color: string | null;
        icon: string | null; // Adicionamos a propriedade 'icon'
        errors: Record<string, string>;
        processing: boolean;
    };
    isUpdate: boolean;
}>();

// Define um evento que será emitido quando o formulário for submetido
const emit = defineEmits(['submit']);

// Lista de ícones pré-definidos que o usuário pode escolher
const availableIcons = [
    'Car', 'Home', 'ShoppingBasket', 'Heart', 'Gift', 'GraduationCap',
    'Utensils', 'Plane', 'Bus', 'Book', 'Film', 'Briefcase', 'HeartPulse'
];
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <DialogHeader>
            <DialogTitle>{{ isUpdate ? 'Edit Category' : 'Register New Category' }}</DialogTitle>
            <DialogDescription>
                Fill in the details for your category below.
            </DialogDescription>
        </DialogHeader>

        <div class="grid gap-4 py-6">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" v-model="form.name" required />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label>Type</Label>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" v-model="form.type" value="expense" class="h-4 w-4">
                        <span>Expense</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" v-model="form.type" value="income" class="h-4 w-4">
                        <span>Income</span>
                    </label>
                </div>
                 <InputError :message="form.errors.type" />
            </div>

            <div class="grid gap-2">
                <Label for="color">Color</Label>
                <div class="relative flex items-center">
                    <Input id="color" v-model="form.color" class="w-full pr-10" />
                    <input type="color" v-model="form.color" class="absolute right-2 h-6 w-6 cursor-pointer bg-transparent border-none">
                </div>
                <InputError :message="form.errors.color" />
            </div>

            <div class="grid gap-2">
                <Label>Icon</Label>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="iconName in availableIcons"
                        :key="iconName"
                        type="button"
                        @click="form.icon = iconName"
                        :class="[
                            'flex h-10 w-10 items-center justify-center rounded-md border transition-colors',
                            form.icon === iconName ? 'bg-primary text-primary-foreground' : 'hover:bg-accent'
                        ]"
                    >
                        <Icon :name="iconName" />
                    </button>
                </div>
                <InputError :message="form.errors.icon" />
            </div>

        </div>

        <DialogFooter>
            <Button type="submit" :disabled="form.processing">
                {{ isUpdate ? 'Update Category' : 'Save Category' }}
            </Button>
        </DialogFooter>
    </form>
</template>
