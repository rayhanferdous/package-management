<template>
    <div class="space-y-6">
        <!-- Basic Information -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
            <div class="space-y-4">
                <div>
                    <label for="name" class="form-label">Package Name *</label>
                    <input type="text"
                           id="name"
                           v-model="form.name"
                           class="form-input"
                           :class="{ 'border-red-500': errors.name }"
                           placeholder="Enter package name">
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>
                
                <div>
                    <label for="description" class="form-label">Short Description *</label>
                    <textarea id="description"
                              v-model="form.description"
                              rows="3"
                              class="form-input"
                              :class="{ 'border-red-500': errors.description }"
                              placeholder="Brief description of the package"></textarea>
                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                </div>
                
                <div>
                    <label for="details" class="form-label">Package Details *</label>
                    <textarea id="details"
                              v-model="form.details"
                              rows="5"
                              class="form-input"
                              :class="{ 'border-red-500': errors.details }"
                              placeholder="Detailed information about the package"></textarea>
                    <p v-if="errors.details" class="mt-1 text-sm text-red-600">{{ errors.details }}</p>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               v-model="form.is_active"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active (visible to users)</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Images -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Package Images</h3>
            <ImageUpload v-model="form.images"
                         :existing-images="existingImages"
                         :max-images="10"
                         :max-size="2"
                         @images-removed="handleImagesRemoved" />
            <p v-if="errors.images" class="mt-2 text-sm text-red-600">{{ errors.images }}</p>
        </div>
        
        <!-- Pricing -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pricing Configuration</h3>
            <PriceManager v-model="form.prices" :errors="errors" />
        </div>
        
        <!-- Actions -->
        <div class="flex justify-end space-x-4 pt-6 border-t">
            <Link :href="route('admin.packages.index')" class="btn-secondary">
                Cancel
            </Link>
            <button type="button"
                    @click="submitForm"
                    :disabled="processing"
                    class="btn-primary flex items-center">
                <FontAwesomeIcon v-if="processing" icon="spinner" class="animate-spin mr-2" />
                {{ processing ? 'Saving...' : 'Save Package' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import ImageUpload from '@/Components/Shared/ImageUpload.vue';
import PriceManager from '@/Components/Admin/Packages/PriceManager.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    package: {
        type: Object,
        default: null
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['submit']);

const form = reactive({
    name: '',
    description: '',
    details: '',
    is_active: true,
    images: [],
    prices: {
        weekday: { price: 0, days: [1,2,3,4] },
        weekend: { price: 0, days: [5,6,0] },
        special_prices: []
    }
});

const existingImages = ref([]);
const deletedImages = ref([]);
const processing = ref(false);

// Populate form if package prop exists
watch(
    () => props.package,
    (pkg) => {
        if (!pkg) return;
        form.name = pkg.name || '';
        form.description = pkg.description || '';
        form.details = pkg.details || '';
        form.is_active = pkg.is_active ?? true;

        // Prices
        form.prices.weekday.price = pkg.prices?.find(p => p.price_type === 'weekday')?.price || 0;
        form.prices.weekday.days = pkg.prices?.find(p => p.price_type === 'weekday')?.days || [1,2,3,4];

        form.prices.weekend.price = pkg.prices?.find(p => p.price_type === 'weekend')?.price || 0;
        form.prices.weekend.days = pkg.prices?.find(p => p.price_type === 'weekend')?.days || [5,6,0];

        form.prices.special_prices = pkg.prices
            ?.filter(p => p.price_type === 'date_range')
            .map(sp => ({
                id: sp.id,
                price: sp.price,
                start_date: sp.start_date,
                end_date: sp.end_date
            })) || [];

        // Images
        existingImages.value = pkg.images || [];
    },
    { immediate: true }
);

const handleImagesRemoved = (imageIds) => {
    deletedImages.value.push(...imageIds);
};

const submitForm = () => {
    processing.value = true;

    const formData = new FormData();

    // Add basic fields
    ['name','description','details','is_active'].forEach(key => {
        formData.append(key, form[key]);
    });

    // Add images
    form.images.forEach((image, index) => {
        if (image.file) formData.append(`images[${index}]`, image.file);
    });

    // Add deleted images
    deletedImages.value.forEach((id, index) => {
        formData.append(`deleted_images[${index}]`, id);
    });

    // Add prices
    formData.append('weekday_price', form.prices.weekday.price);
    formData.append('weekend_price', form.prices.weekend.price);
    formData.append('weekday_days', JSON.stringify(form.prices.weekday.days));
    formData.append('weekend_days', JSON.stringify(form.prices.weekend.days));

    // Add special prices
    form.prices.special_prices.forEach((sp, index) => {
        formData.append(`special_prices[${index}][id]`, sp.id || '');
        formData.append(`special_prices[${index}][price]`, sp.price);
        formData.append(`special_prices[${index}][start_date]`, sp.start_date);
        formData.append(`special_prices[${index}][end_date]`, sp.end_date);
    });

    emit('submit', formData);
};

// Reset processing when errors change
watch(() => props.errors, () => { processing.value = false });
</script>
