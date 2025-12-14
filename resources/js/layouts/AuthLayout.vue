<script setup>
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
