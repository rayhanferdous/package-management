<template>
    <AdminLayout :title="'Create Package'" 
                 :subtitle="'Add a new travel package'"
                 :breadcrumbs="['Packages', 'Create']">
        <PackageForm :errors="errors" @submit="submitForm" />
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
import PackageForm from '@/Components/Admin/Packages/PackageForm.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    errors: {
        type: Object,
        default: () => ({})
    }
});

const submitForm = (formData) => {
    router.post(route('admin.packages.store'), formData, {
        preserveScroll: true
    });
};
</script>