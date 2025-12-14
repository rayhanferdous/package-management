<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">My Purchases</h1>
                <p class="text-gray-600">View your booking history and download receipts</p>
            </div>

            <!-- Filters -->
            <div class="card mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative flex-1">
                        <input type="text"
                               v-model="filters.search"
                               @input="debouncedSearch"
                               placeholder="Search purchases..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-red-500 focus:outline-none">
                        <FontAwesomeIcon icon="search" class="absolute left-3 top-3 text-gray-400" />
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <select v-model="filters.status"
                                @change="updateFilters"
                                class="form-input py-2">
                            <option value="all">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                        
                        <div class="flex space-x-2">
                            <input type="date"
                                   v-model="filters.start_date"
                                   @change="updateFilters"
                                   class="form-input py-2">
                            <input type="date"
                                   v-model="filters.end_date"
                                   @change="updateFilters"
                                   class="form-input py-2">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchases Table -->
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Package
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="purchase in purchases.data" :key="purchase.id">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 mr-4">
                                            <img v-if="purchase.package.images?.length > 0"
                                                 :src="`/storage/${purchase.package.images[0].image_path}`"
                                                 alt="Package"
                                                 class="h-10 w-10 rounded-lg object-cover">
                                            <div v-else
                                                 class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <FontAwesomeIcon icon="box" class="text-gray-400" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                {{ purchase.package.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Transaction: {{ purchase.stripe_payment_id?.slice(-8) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(purchase.created_at) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-semibold text-gray-900">
                                        ${{ purchase.amount }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="statusClass(purchase.status)"
                                          class="badge">
                                        {{ purchase.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link :href="route('purchases.show', purchase)"
                                          class="text-blue-600 hover:text-blue-900 mr-4">
                                        View Details
                                    </Link>
                                    <button v-if="purchase.status === 'completed'"
                                            @click="downloadReceipt(purchase)"
                                            class="text-green-600 hover:text-green-900">
                                        Receipt
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div v-if="purchases.data.length === 0" class="text-center py-12">
                    <FontAwesomeIcon icon="shopping-cart" class="text-gray-400 text-4xl mb-3" />
                    <p class="text-gray-500">No purchases found</p>
                    <Link :href="route('packages.index')"
                          class="btn-primary mt-4">
                        Browse Packages
                    </Link>
                </div>
                
                <!-- Pagination -->
                <div v-if="purchases.data.length > 0" class="px-6 py-4 border-t">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ purchases.from }} to {{ purchases.to }} of {{ purchases.total }} results
                        </div>
                        <div class="flex space-x-2">
                            <Link v-for="(link, index) in purchases.links"
                                  :key="index"
                                  :href="link.url || '#'"
                                  :class="[
                                      'px-3 py-1 rounded-md',
                                      link.active ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                  ]"
                                  v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { debounce } from 'lodash';

defineProps({
    purchases: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: 'all',
            start_date: '',
            end_date: ''
        })
    }
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const statusClass = (status) => {
    switch (status.toLowerCase()) {
        case 'completed':
            return 'badge-success';
        case 'pending':
            return 'badge-warning';
        case 'failed':
            return 'badge-danger';
        default:
            return 'badge-info';
    }
};

const downloadReceipt = (purchase) => {
    // Generate receipt PDF or show receipt page
    window.open(route('purchases.show', purchase), '_blank');
};

// Debounced search
const debouncedSearch = debounce(() => {
    updateFilters();
}, 500);

const updateFilters = () => {
    router.get(route('purchases.index'), props.filters, {
        preserveState: true,
        replace: true
    });
};
</script>