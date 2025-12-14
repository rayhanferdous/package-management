<template>
    <AdminLayout :title="'User Management'" 
                 :subtitle="'Manage users and permissions'"
                 :breadcrumbs="['Super Admin', 'Users']">
        <UserManagement :users="users" :roles="roles" :filters="filters" />
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
import UserManagement from '@/Components/SuperAdmin/UserManagement.vue';

defineProps({
    users: {
        type: Object,
        required: true
    },
    roles: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            role: 'all'
        })
    }
});
</script>