<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
        <!-- Image -->
        <div class="relative h-48 overflow-hidden">
            <img :src="imageUrl" :alt="package.name" class="w-full h-full object-cover gallery-image">

            <!-- Price Badge -->
            <div class="absolute top-4 right-4 bg-white rounded-lg shadow-lg px-3 py-2">
                <div class="text-sm text-gray-500">From</div>
                <div class="text-xl font-bold text-blue-600">${{ currentPrice }}</div>
            </div>

            <!-- Status Badge -->
            <div v-if="!package.is_active"
                class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm">
                Unavailable
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">{{ package.name }}</h3>
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ package.description }}</p>

            <!-- Pricing Info -->
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Weekday:</span>
                    <span class="font-semibold">${{ weekdayPrice }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Weekend:</span>
                    <span class="font-semibold">${{ weekendPrice }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <Link :href="route('packages.show', package)" class="btn-primary flex-1 text-center">
                    View Details
                </Link>
                <Link v-if="package.is_active && $page.props.auth.user" :href="route('purchase.create', package)"
                    class="btn-success flex-1 text-center">
                    Book Now
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    package: {
        type: Object,
        required: true
    }
});

const imageUrl = computed(() => {
    if (props.package.images?.length > 0) {
        const image = props.package.images[0];
        return image.image_path.startsWith('http') ? image.image_path : `/storage/${image.image_path}`;
    }
    return '/images/placeholder.jpg';
});

const currentPrice = computed(() => {
    const price = props.package.current_price;
    return price ? price.price.toFixed(2) : '0.00';
});

const weekdayPrice = computed(() => {
    const price = props.package.prices?.find(p => p.price_type === 'weekday');
    return price ? price.price.toFixed(2) : '0.00';
});

const weekendPrice = computed(() => {
    const price = props.package.prices?.find(p => p.price_type === 'weekend');
    return price ? price.price.toFixed(2) : '0.00';
});
</script>

<style scoped>
@reference utilities;

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>