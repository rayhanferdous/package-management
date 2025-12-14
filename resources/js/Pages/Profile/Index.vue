<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Profile Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
                <p class="text-gray-600">Manage your account information and preferences</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Profile Info -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Personal Information -->
                    <div class="card">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
                            <Link :href="route('profile.edit')"
                                  class="text-blue-600 hover:text-blue-700">
                                Edit Profile
                            </Link>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-24 text-gray-500">Name</div>
                                <div class="font-medium">{{ user.name }}</div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-24 text-gray-500">Email</div>
                                <div class="font-medium">{{ user.email }}</div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-24 text-gray-500">Phone</div>
                                <div class="font-medium">{{ user.phone || 'Not set' }}</div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-24 text-gray-500">Joined</div>
                                <div class="font-medium">{{ formatDate(user.created_at) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Purchases -->
                    <div class="card">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Purchases</h3>
                            <Link :href="route('profile.purchases')"
                                  class="text-blue-600 hover:text-blue-700">
                                View All
                            </Link>
                        </div>
                        
                        <div v-if="user.purchases?.length > 0" class="space-y-4">
                            <div v-for="purchase in user.purchases" 
                                 :key="purchase.id"
                                 class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-lg overflow-hidden mr-4">
                                        <img v-if="purchase.package.images?.length > 0"
                                             :src="`/storage/${purchase.package.images[0].image_path}`"
                                             alt="Package"
                                             class="w-full h-full object-cover">
                                        <div v-else
                                             class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <FontAwesomeIcon icon="box" class="text-gray-400" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ purchase.package.name }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ formatDate(purchase.created_at) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">${{ purchase.amount }}</div>
                                    <span :class="statusClass(purchase.status)"
                                          class="badge text-xs">
                                        {{ purchase.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-8">
                            <FontAwesomeIcon icon="shopping-cart" class="text-gray-400 text-4xl mb-3" />
                            <p class="text-gray-500">No recent purchases</p>
                            <Link :href="route('packages.index')"
                                  class="btn-primary mt-4">
                                Browse Packages
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Actions -->
                <div class="space-y-6">
                    <!-- Avatar -->
                    <div class="card text-center">
                        <div class="relative w-32 h-32 mx-auto mb-4">
                            <div v-if="user.avatar" 
                                 class="w-full h-full rounded-full overflow-hidden">
                                <img :src="`/storage/${user.avatar}`"
                                     alt="Avatar"
                                     class="w-full h-full object-cover">
                            </div>
                            <div v-else
                                 class="w-full h-full rounded-full bg-blue-100 flex items-center justify-center">
                                <FontAwesomeIcon icon="user" class="text-blue-600 text-4xl" />
                            </div>
                        </div>
                        <h4 class="font-semibold text-gray-800">{{ user.name }}</h4>
                        <p class="text-sm text-gray-500 mb-4">{{ user.email }}</p>
                        
                        <div class="space-y-2">
                            <form @submit.prevent="updateAvatar">
                                <input type="file"
                                       ref="avatarInput"
                                       @change="handleAvatarChange"
                                       accept="image/*"
                                       class="hidden">
                                <button type="button"
                                        @click="$refs.avatarInput.click()"
                                        class="w-full btn-secondary text-sm">
                                    Change Avatar
                                </button>
                            </form>
                            <button v-if="user.avatar"
                                    @click="deleteAvatar"
                                    class="w-full btn-danger text-sm">
                                Remove Avatar
                            </button>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <h4 class="font-semibold text-gray-800 mb-4">Quick Actions</h4>
                        <div class="space-y-3">
                            <Link :href="route('profile.update.password')"
                                  class="flex items-center p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                                <FontAwesomeIcon icon="key" class="mr-3" />
                                Change Password
                            </Link>
                            <Link :href="route('profile.purchases')"
                                  class="flex items-center p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                                <FontAwesomeIcon icon="history" class="mr-3" />
                                Purchase History
                            </Link>
                            <Link :href="route('packages.index')"
                                  class="flex items-center p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                                <FontAwesomeIcon icon="box" class="mr-3" />
                                Browse Packages
                            </Link>
                        </div>
                    </div>

                    <!-- Account Status -->
                    <div class="card">
                        <h4 class="font-semibold text-gray-800 mb-4">Account Status</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Account Type</span>
                                <span class="font-semibold">{{ user.roles?.[0]?.name || 'User' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status</span>
                                <span class="badge-success">Active</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Member Since</span>
                                <span>{{ new Date(user.created_at).getFullYear() }}</span>
                            </div>
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

defineProps({
    user: {
        type: Object,
        required: true
    }
});

const avatarInput = ref(null);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
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

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('avatar', file);

    router.post(route('profile.update.avatar'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            event.target.value = '';
        }
    });
};

const deleteAvatar = () => {
    router.delete(route('profile.delete.avatar'), {
        preserveScroll: true
    });
};
</script>