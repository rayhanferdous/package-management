<template>
    <AdminLayout title="Dashboard" subtitle="Overview of your package management system" :breadcrumbs="['Dashboard']">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Packages -->
            <div class="p-6 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Packages</p>
                        <p class="text-3xl font-bold text-gray-800">{{ stats.total_packages || 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <FontAwesomeIcon icon="box" class="text-blue-600 text-2xl" />
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <FontAwesomeIcon icon="check" class="text-green-500 mr-1" />
                        <span>{{ stats.active_packages || 0 }} active</span>
                    </div>
                </div>
            </div>

            <!-- Total Purchases -->
            <div class="p-6 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Purchases</p>
                        <p class="text-3xl font-bold text-gray-800">{{ stats.total_purchases || 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <FontAwesomeIcon icon="shopping-cart" class="text-green-600 text-2xl" />
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    Revenue: ${{ stats.total_revenue?.toLocaleString() || '0' }}
                </div>
            </div>

            <!-- Total Users (Super Admin only) -->
            <div v-if="$page.props.auth.user.roles?.some(r => r.name === 'Super Admin')"
                class="p-6 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="text-3xl font-bold text-gray-800">{{ stats.total_users || 0 }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <FontAwesomeIcon icon="users" class="text-purple-600 text-2xl" />
                    </div>
                </div>
                <div class="mt-4">
                    <Link :href="route('super-admin.users.index')"
                        class="text-sm text-purple-600 hover:text-purple-700">
                        Manage Users →
                    </Link>
                </div>
            </div>

            <!-- System Status -->
            <div class="p-6 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">System Status</p>
                        <p class="text-3xl font-bold text-green-600">Active</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <FontAwesomeIcon icon="check-circle" class="text-green-600 text-2xl" />
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    Last updated: {{ new Date().toLocaleDateString() }}
                </div>
            </div>
        </div>

        <!-- Recent Purchases -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="p-6 bg-white rounded-lg shadow">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Purchases</h3>
                        <Link :href="route('admin.purchases.index')" class="text-sm text-blue-600 hover:text-blue-700">
                            View All →
                        </Link>
                    </div>

                    <div v-if="stats.recent_purchases?.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Package</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="purchase in stats.recent_purchases" :key="purchase.id">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                        purchase.package.name }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ purchase.user.name }}
                                        <div class="text-gray-500 text-xs">{{ purchase.user.email }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">${{
                                        purchase.amount }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span :class="statusClass(purchase.status)"
                                            class="px-2 py-1 rounded-full text-xs">
                                            {{ purchase.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-8 text-gray-500">
                        <FontAwesomeIcon icon="shopping-cart" class="text-4xl mb-3" />
                        No recent purchases
                    </div>
                </div>
            </div>

            <!-- Quick Actions + Top Packages -->
            <div>
                <!-- Quick Actions -->
                <div class="p-6 bg-white rounded-lg shadow mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <Link :href="route('admin.packages.create')"
                            class="flex items-center p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">
                            <FontAwesomeIcon icon="plus" class="mr-3" /> Create New Package
                        </Link>
                        <Link :href="route('admin.packages.index')"
                            class="flex items-center p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                            <FontAwesomeIcon icon="box" class="mr-3" /> Manage Packages
                        </Link>
                        <Link :href="route('admin.purchases.index')"
                            class="flex items-center p-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">
                            <FontAwesomeIcon icon="shopping-cart" class="mr-3" /> View Purchases
                        </Link>
                    </div>
                </div>

                <!-- Top Packages -->
                <div class="p-6 bg-white rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Packages</h3>
                    <div class="space-y-4">
                        <div v-for="pkg in stats.top_packages || []" :key="pkg.id"
                            class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ pkg.name }}</div>
                                <div class="text-sm text-gray-500">{{ pkg.purchase_count || 0 }} purchases</div>
                            </div>
                            <div class="font-semibold text-green-600">${{ pkg.revenue || 0 }}</div>
                        </div>
                        <div v-if="(!stats.top_packages || stats.top_packages.length === 0)"
                            class="text-center py-4 text-gray-500">
                            No package data available
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    stats: {
        type: Object,
        required: true
    }
});

const statusClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'failed': return 'bg-red-100 text-red-800';
        default: return 'bg-blue-100 text-blue-800';
    }
};
</script>
