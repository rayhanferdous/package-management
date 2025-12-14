<template>
    <div class="space-y-6">
        <!-- Search and Filters -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative flex-1">
                <input type="text"
                       v-model="filters.search"
                       @input="debouncedSearch"
                       placeholder="Search packages..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <FontAwesomeIcon icon="search" class="absolute left-3 top-3 text-gray-400" />
            </div>
            
            <div class="flex items-center space-x-4">
                <select v-model="filters.status"
                        @change="updateFilters"
                        class="form-input py-2">
                    <option value="all">All Status</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive Only</option>
                </select>
                
                <Link :href="route('admin.packages.create')"
                      class="btn-primary flex items-center">
                    <FontAwesomeIcon icon="plus" class="mr-2" />
                    New Package
                </Link>
            </div>
        </div>
        
        <!-- Packages Table -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="pkg in packages.data" :key="pkg.id">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img v-if="pkg.images?.length > 0"
                                             :src="`/storage/${pkg.images[0].image_path}`"
                                             alt="Package image"
                                             class="h-10 w-10 rounded-full object-cover">
                                        <div v-else
                                             class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <FontAwesomeIcon icon="box" class="text-gray-400" />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ pkg.name }}</div>
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ pkg.description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ pkg.images?.length || 0 }} images</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>Weekday: ${{ getPrice(pkg, 'weekday') }}</div>
                                <div>Weekend: ${{ getPrice(pkg, 'weekend') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="pkg.is_active ? 'badge-success' : 'badge-warning'" class="badge">
                                    {{ pkg.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <Link :href="route('admin.packages.edit', pkg)" class="text-blue-600 hover:text-blue-900">
                                        <FontAwesomeIcon icon="edit" />
                                    </Link>
                                    <Link :href="route('packages.show', pkg)" target="_blank" class="text-green-600 hover:text-green-900">
                                        <FontAwesomeIcon icon="eye" />
                                    </Link>
                                    <button v-if="$page.props.auth.user.roles?.some(r => r.name === 'Super Admin')"
                                            @click="confirmDelete(pkg)"
                                            class="text-red-600 hover:text-red-900">
                                        <FontAwesomeIcon icon="trash" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Empty State -->
            <div v-if="packages.data.length === 0" class="text-center py-12">
                <FontAwesomeIcon icon="box-open" class="text-gray-400 text-4xl mb-3" />
                <p class="text-gray-500">No packages found</p>
                <p class="text-gray-400 text-sm mt-1">Create your first package to get started</p>
                <Link :href="route('admin.packages.create')" class="btn-primary mt-4 inline-flex items-center">
                    <FontAwesomeIcon icon="plus" class="mr-2" />
                    Create Package
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="packages.data.length > 0" class="px-6 py-4 border-t">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ packages.from }} to {{ packages.to }} of {{ packages.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <Link v-for="(link, index) in packages.links"
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
        
        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex items-center mb-4">
                    <FontAwesomeIcon icon="exclamation-triangle" class="text-red-500 text-2xl mr-3" />
                    <h3 class="text-lg font-semibold text-gray-800">Delete Package</h3>
                </div>
                
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete "<span class="font-semibold">{{ packageToDelete?.name }}</span>"? This action cannot be undone.
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
                    <button @click="deletePackage" :disabled="deleting" class="btn-danger flex items-center">
                        <FontAwesomeIcon v-if="deleting" icon="spinner" class="animate-spin mr-2" />
                        {{ deleting ? 'Deleting...' : 'Delete Package' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    packages: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '', status: 'all' }) }
});

const showDeleteModal = ref(false);
const packageToDelete = ref(null);
const deleting = ref(false);

const getPrice = (pkg, type) => {
    const price = pkg.prices?.find(p => p.price_type === type);

    if (!price || price.price === null || price.price === undefined) {
        return '0.00';
    }

    return Number(price.price).toFixed(2);
};


const confirmDelete = (pkg) => {
    packageToDelete.value = pkg;
    showDeleteModal.value = true;
};

const deletePackage = () => {
    if (!packageToDelete.value) return;

    deleting.value = true;

    router.delete(route('admin.packages.destroy', packageToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            packageToDelete.value = null;
        },
        onFinish: () => { deleting.value = false; }
    });
};

// Debounced search
const debouncedSearch = debounce(() => updateFilters(), 500);

const updateFilters = () => {
    router.get(route('admin.packages.index'), props.filters, {
        preserveState: true,
        replace: true
    });
};

// Watch for filter changes
watch(() => props.filters, () => updateFilters(), { deep: true });
</script>
