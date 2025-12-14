<template>
    <AdminDashboard :stats="stats" />
</template>

<script setup>
import AdminDashboard from '@/Components/Admin/Dashboard.vue';

defineProps({
    stats: {
        type: Object,
        required: true
    }
});
</script>