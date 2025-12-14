<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <Sidebar />
        
        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b">
                <div class="flex justify-between items-center px-6 py-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ title }}</h1>
                        <p class="text-gray-600">{{ subtitle }}</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Breadcrumb -->
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                <li class="inline-flex items-center">
                                    <Link :href="route('admin.dashboard')" 
                                          class="text-gray-700 hover:text-blue-600">
                                        Admin
                                    </Link>
                                </li>
                                <li v-for="(crumb, index) in breadcrumbs" :key="index">
                                    <div class="flex items-center">
                                        <FontAwesomeIcon icon="chevron-right" class="text-gray-400 mx-2" />
                                        <span class="text-gray-500">{{ crumb }}</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        
                        <!-- Quick Actions -->
                        <div class="flex items-center space-x-2">
                            <Link v-if="$page.props.auth.user.roles?.some(r => r.name === 'Super Admin')"
                                  :href="route('super-admin.settings.index')"
                                  class="p-2 text-gray-600 hover:text-blue-600 transition">
                                <FontAwesomeIcon icon="cog" />
                            </Link>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '@/Components/Shared/Sidebar.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    },
    breadcrumbs: {
        type: Array,
        default: () => []
    }
});
</script>