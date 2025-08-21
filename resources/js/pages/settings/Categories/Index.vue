<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

import { type BreadcrumbItem } from '@/types';


const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/categories',
    },
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Categories" />


    </AppLayout>
</template>
