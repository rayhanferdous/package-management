<template>
    <AppLayout>
        <!-- Package Details -->
        <div class="container mx-auto px-4 py-8">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <Link :href="route('packages.index')" 
                              class="text-gray-700 hover:text-blue-600">
                            Packages
                        </Link>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <FontAwesomeIcon icon="chevron-right" class="text-gray-400 mx-2" />
                            <span class="text-gray-500">{{ package.name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column - Images -->
                <div>
                    <PackageGallery :package="package" />
                </div>

                <!-- Right Column - Details -->
                <!-- <div>
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ package.name }}</h1>
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="text-3xl font-bold text-blue-600">
                                ${{ currentPrice.price.toFixed(2) }}
                            </div>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                {{ formatPriceType(currentPrice.price_type) }}
                            </span>
                        </div>
                        <p class="text-gray-600">{{ package.description }}</p>
                    </div>

                    <div class="card mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Pricing Breakdown</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Weekday Price</span>
                                <span class="font-semibold">${{ weekdayPrice.price.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Weekend Price</span>
                                <span class="font-semibold">${{ weekendPrice.price.toFixed(2) }}</span>
                            </div>
                            
                          
                            <div v-if="specialPrices.length > 0">
                                <div class="text-sm font-medium text-gray-700 mb-2">Special Prices:</div>
                                <div v-for="special in specialPrices" 
                                     :key="special.id"
                                     class="flex justify-between items-center mb-2 text-sm">
                                    <span class="text-gray-600">
                                        {{ formatDate(special.start_date) }} - {{ formatDate(special.end_date) }}
                                    </span>
                                    <span class="font-semibold text-green-600">${{ special.price.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                   
                    <div class="card mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Package Details</h3>
                        <div class="prose max-w-none text-gray-600">
                            <p>{{ package.details }}</p>
                        </div>
                    </div>

                    
                    <div class="space-y-4">
                        <Link v-if="package.is_active && $page.props.auth.user"
                              :href="route('purchase.create', package)"
                              class="w-full btn-primary py-3 text-lg flex items-center justify-center">
                            <FontAwesomeIcon icon="shopping-cart" class="mr-2" />
                            Book Now
                        </Link>
                        
                        <div v-if="!$page.props.auth.user" class="text-center">
                            <p class="text-gray-600 mb-2">Please login to book this package</p>
                            <Link :href="route('login')"
                                  class="btn-primary">
                                Login to Book
                            </Link>
                        </div>
                        
                        <div v-if="!package.is_active" 
                             class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <FontAwesomeIcon icon="exclamation-triangle" class="text-red-500 mr-2" />
                            <span class="text-red-700">This package is currently unavailable</span>
                        </div>
                    </div>

                    <div v-if="$page.props.auth.user?.roles?.some(r => ['Admin', 'Super Admin'].includes(r.name))"
                         class="mt-6 pt-6 border-t">
                        <h4 class="text-sm font-semibold text-gray-500 mb-2">Admin Actions</h4>
                        <div class="flex space-x-3">
                            <Link :href="route('admin.packages.edit', package)"
                                  class="btn-secondary text-sm">
                                Edit Package
                            </Link>
                            <Link :href="route('admin.packages.index')"
                                  class="btn-secondary text-sm">
                                Back to Admin
                            </Link>
                        </div>
                    </div>
                </div> -->
            </div>

            <!-- Related Packages -->
            <div v-if="relatedPackages.length > 0" class="mt-16">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">You Might Also Like</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <PackageCard v-for="related in relatedPackages" 
                                 :key="related.id"
                                 :package="related" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PackageGallery from '@/Components/Public/PackageGallery.vue';
import PackageCard from '@/Components/Public/PackageCard.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    package: {
        type: Object,
        required: true
    },
    currentPrice: {
        type: Object,
        required: true
    },
    weekdayPrice: {
        type: Object,
        required: true
    },
    weekendPrice: {
        type: Object,
        required: true
    },
    specialPrices: {
        type: Array,
        default: () => []
    },
    relatedPackages: {
        type: Array,
        default: () => []
    }
});

const formatPriceType = (type) => {
    const types = {
        weekday: 'Weekday Rate',
        weekend: 'Weekend Rate',
        date_range: 'Special Rate'
    };
    return types[type] || type;
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};
</script>