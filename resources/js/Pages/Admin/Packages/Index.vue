<template>
    <AdminLayout :title="'Package Management'" 
                 :subtitle="'Create and manage travel packages'"
                 :breadcrumbs="['Packages']">
        <PackageList :packages="packages" :filters="filters" />
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
import PackageList from '@/Components/Admin/Packages/PackageList.vue';

defineProps({
    packages: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: 'all'
        })
    }
});
</script>