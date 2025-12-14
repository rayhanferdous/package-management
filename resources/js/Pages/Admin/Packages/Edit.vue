<template>
    <AdminLayout
        title="Edit Package"
        subtitle="Update package details"
        :breadcrumbs="['Packages', 'Edit']"
    >
        <PackageForm
            :package="packageData"
            :errors="errors"
            @submit="submitForm"
        />
    </AdminLayout>
</template>


<script setup>
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
import PackageForm from '@/Components/Admin/Packages/PackageForm.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    packageData: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const submitForm = (formData) => {
    router.post(
        route('admin.packages.update', props.packageData.id),
        {
            _method: 'PUT',
            ...formData
        },
        {
            preserveScroll: true
        }
    );
};
</script>
