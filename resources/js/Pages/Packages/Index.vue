<template>
    <AppLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Amazing Travel Packages</h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto">
                    Discover unforgettable experiences with our curated collection of travel packages
                </p>
                <div class="max-w-md mx-auto">
                    <div class="relative">
                        <input type="text"
                               v-model="search"
                               @keyup.enter="performSearch"
                               placeholder="Search packages..."
                               class="w-full pl-12 pr-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <FontAwesomeIcon icon="search" class="absolute left-4 top-3.5 text-gray-400" />
                        <button @click="performSearch"
                                class="absolute right-2 top-2 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <section class="bg-white py-6 border-b">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="text-gray-600">
                        Showing {{ packages.total }} packages
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Price Range -->
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Price:</span>
                            <input type="number"
                                   v-model="filters.min_price"
                                   @change="updateFilters"
                                   placeholder="Min"
                                   class="w-20 form-input text-sm py-1">
                            <span class="text-gray-400">-</span>
                            <input type="number"
                                   v-model="filters.max_price"
                                   @change="updateFilters"
                                   placeholder="Max"
                                   class="w-20 form-input text-sm py-1">
                        </div>
                        
                        <!-- Sort -->
                        <select v-model="filters.sort"
                                @change="updateFilters"
                                class="form-input py-2 text-sm">
                            <option value="newest">Newest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <!-- Packages Grid -->
        <section class="py-12">
            <div class="container mx-auto px-4">
                <!-- Packages -->
                <div v-if="packages.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <PackageCard v-for="pkg in packages.data" 
                                 :key="pkg.id"
                                 :package="pkg" />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16">
                    <FontAwesomeIcon icon="box-open" class="text-gray-400 text-5xl mb-4" />
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">No packages found</h3>
                    <p class="text-gray-600 mb-6">Try adjusting your search or filters</p>
                    <button @click="clearFilters"
                            class="btn-primary">
                        Clear Filters
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="packages.data.length > 0" class="mt-12">
                    <div class="flex justify-center">
                        <div class="flex space-x-2">
                            <Link v-for="(link, index) in packages.links"
                                  :key="index"
                                  :href="link.url || '#'"
                                  :class="[
                                      'px-4 py-2 rounded-md',
                                      link.active ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                  ]"
                                  v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Why Choose Us?</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <FontAwesomeIcon icon="shield-alt" class="text-blue-600 text-2xl" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Secure Booking</h3>
                        <p class="text-gray-600">Your payments are protected with industry-leading security</p>
                    </div>
                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <FontAwesomeIcon icon="money-bill-wave" class="text-green-600 text-2xl" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Best Prices</h3>
                        <p class="text-gray-600">We guarantee the best prices for all our packages</p>
                    </div>
                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <FontAwesomeIcon icon="headset" class="text-purple-600 text-2xl" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">24/7 Support</h3>
                        <p class="text-gray-600">Our support team is always ready to help you</p>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PackageCard from '@/Components/Public/PackageCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    packages: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            min_price: '',
            max_price: '',
            sort: 'newest'
        })
    },
    priceRange: {
        type: Object,
        default: () => ({
            min: 0,
            max: 1000
        })
    }
});

const search = ref('');

const performSearch = () => {
    router.get(route('packages.index'), { search: search.value }, {
        preserveState: true,
        replace: true
    });
};

const updateFilters = debounce(() => {
    router.get(route('packages.index'), props.filters, {
        preserveState: true,
        replace: true
    });
}, 500);

const clearFilters = () => {
    router.get(route('packages.index'));
};

watch(() => props.filters, () => {
    updateFilters();
}, { deep: true });
</script>
