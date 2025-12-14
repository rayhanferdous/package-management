<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800">User Management</h3>
                <p class="text-gray-600">Manage users and their permissions</p>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text"
                           v-model="filters.search"
                           @input="debouncedSearch"
                           placeholder="Search users..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-red-500 focus:outline-none">
                    <FontAwesomeIcon icon="search" class="absolute left-3 top-3 text-gray-400" />
                </div>
                
                <select v-model="filters.role"
                        @change="updateFilters"
                        class="form-input py-2">
                    <option value="all">All Roles</option>
                    <option v-for="role in roles" :key="role.id" :value="role.name">
                        {{ role.name }}
                    </option>
                </select>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joined
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in users.data" :key="user.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <FontAwesomeIcon icon="user" class="text-blue-600" />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ user.email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="roleClass(user.roles[0]?.name)"
                                      class="badge">
                                    {{ user.roles[0]?.name || 'User' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="user.is_active ? 'badge-success' : 'badge-danger'"
                                      class="badge">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <!-- Edit -->
                                    <Link :href="route('super-admin.users.edit', user)"
                                          class="text-blue-600 hover:text-blue-900">
                                        <FontAwesomeIcon icon="edit" />
                                    </Link>
                                    
                                    <!-- Promote/Demote -->
                                    <template v-if="user.id !== $page.props.auth.user.id">
                                        <button v-if="!user.roles?.some(r => ['Admin', 'Super Admin'].includes(r.name))"
                                                @click="promoteUser(user)"
                                                class="text-green-600 hover:text-green-900">
                                            <FontAwesomeIcon icon="arrow-up" />
                                        </button>
                                        <button v-else-if="!user.roles?.some(r => r.name === 'Super Admin')"
                                                @click="demoteUser(user)"
                                                class="text-yellow-600 hover:text-yellow-900">
                                            <FontAwesomeIcon icon="arrow-down" />
                                        </button>
                                    </template>
                                    
                                    <!-- Toggle Status -->
                                    <button v-if="user.id !== $page.props.auth.user.id"
                                            @click="toggleUserStatus(user)"
                                            :class="user.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'">
                                        <FontAwesomeIcon :icon="user.is_active ? 'ban' : 'check'" />
                                    </button>
                                    
                                    <!-- Delete -->
                                    <button v-if="user.id !== $page.props.auth.user.id && !user.roles?.some(r => r.name === 'Super Admin')"
                                            @click="confirmDelete(user)"
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
            <div v-if="users.data.length === 0" class="text-center py-12">
                <FontAwesomeIcon icon="users" class="text-gray-400 text-4xl mb-3" />
                <p class="text-gray-500">No users found</p>
            </div>
            
            <!-- Pagination -->
            <div v-if="users.data.length > 0" class="px-6 py-4 border-t">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <Link v-for="(link, index) in users.links"
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete User</h3>
                </div>
                
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete "<span class="font-semibold">{{ userToDelete?.name }}</span>"? 
                    This action cannot be undone.
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false"
                            class="btn-secondary">
                        Cancel
                    </button>
                    <button @click="deleteUser"
                            :disabled="deleting"
                            class="btn-danger flex items-center">
                        <FontAwesomeIcon v-if="deleting" icon="spinner" class="animate-spin mr-2" />
                        {{ deleting ? 'Deleting...' : 'Delete User' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
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

const showDeleteModal = ref(false);
const userToDelete = ref(null);
const deleting = ref(false);

const roleClass = (role) => {
    switch (role) {
        case 'Super Admin':
            return 'badge-danger';
        case 'Admin':
            return 'badge-warning';
        default:
            return 'badge-info';
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const confirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    if (!userToDelete.value) return;
    
    deleting.value = true;
    
    router.delete(route('super-admin.users.destroy', userToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};

const promoteUser = (user) => {
    router.post(route('super-admin.users.promote', user), {
        preserveScroll: true
    });
};

const demoteUser = (user) => {
    router.post(route('super-admin.users.demote', user), {
        preserveScroll: true
    });
};

const toggleUserStatus = (user) => {
    router.post(route('super-admin.users.toggle-status', user), {
        preserveScroll: true
    });
};

// Debounced search
const debouncedSearch = debounce(() => {
    updateFilters();
}, 500);

const updateFilters = () => {
    router.get(route('super-admin.users.index'), props.filters, {
        preserveState: true,
        replace: true
    });
};
</script>