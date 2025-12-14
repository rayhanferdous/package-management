<template>
    <aside class="w-64 bg-gray-800 text-white min-h-screen">
        <div class="p-6">
            <Link :href="route('admin.dashboard')" class="flex items-center mb-8">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                    <FontAwesomeIcon icon="box" class="text-white text-xl" />
                </div>
                <span class="text-xl font-bold">Admin Panel</span>
            </Link>
            
            <nav class="space-y-2">
                <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">
                    Main
                </div>
                
                <Link :href="route('admin.dashboard')" 
                      class="flex items-center px-4 py-3 rounded-lg transition"
                      :class="isActive('admin.dashboard') ? 'bg-blue-600 text-white' : 'hover:bg-gray-700'">
                    <FontAwesomeIcon icon="home" class="mr-3" />
                    Dashboard
                </Link>
                
                <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-6 mb-2">
                    Management
                </div>
                
                <Link :href="route('admin.packages.index')" 
                      class="flex items-center px-4 py-3 rounded-lg transition"
                      :class="isActive('admin.packages.index') ? 'bg-blue-600 text-white' : 'hover:bg-gray-700'">
                    <FontAwesomeIcon icon="box" class="mr-3" />
                    Packages
                </Link>
                
                <Link :href="route('admin.purchases.index')" 
                      class="flex items-center px-4 py-3 rounded-lg transition"
                      :class="isActive('admin.purchases.index') ? 'bg-blue-600 text-white' : 'hover:bg-gray-700'">
                    <FontAwesomeIcon icon="shopping-cart" class="mr-3" />
                    Purchases
                </Link>
                
                <!-- Super Admin Links -->
                <template v-if="$page.props.auth.user.roles?.some(r => r.name === 'Super Admin')">
                    <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-6 mb-2">
                        Super Admin
                    </div>
                    
                    <Link :href="route('super-admin.users.index')" 
                          class="flex items-center px-4 py-3 rounded-lg transition"
                          :class="isActive('super-admin.users.index') ? 'bg-blue-600 text-white' : 'hover:bg-gray-700'">
                        <FontAwesomeIcon icon="users" class="mr-3" />
                        User Management
                    </Link>
                    
                    <Link :href="route('super-admin.settings.index')" 
                          class="flex items-center px-4 py-3 rounded-lg transition"
                          :class="isActive('super-admin.settings.index') ? 'bg-blue-600 text-white' : 'hover:bg-gray-700'">
                        <FontAwesomeIcon icon="cog" class="mr-3" />
                        Settings
                    </Link>
                </template>
                
                <div class="border-t border-gray-700 mt-6 pt-4">
                    <Link :href="route('packages.index')" 
                          class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <FontAwesomeIcon icon="arrow-left" class="mr-3" />
                        Back to Site
                    </Link>
                </div>
            </nav>
        </div>
        
        <!-- User Info -->
        <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-gray-700">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                    <FontAwesomeIcon icon="user" class="text-blue-600" />
                </div>
                <div class="flex-1">
                    <div class="font-medium">{{ $page.props.auth.user.name }}</div>
                    <div class="text-gray-400 text-sm">
                        {{ $page.props.auth.user.roles?.[0]?.name }}
                    </div>
                </div>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const isActive = (routeName) => {
    return page.url.startsWith(route(routeName).split('?')[0]);
};
</script>