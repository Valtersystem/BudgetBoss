<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Breadcrumbs para navegação
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    }
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Accounts" />
    </AppLayout>
</template>
