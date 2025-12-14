<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <Navbar />
        
        <!-- Page Content -->
        <main class="pt-16">
            <slot />
        </main>
        
        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-8 mt-12">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h3 class="text-xl font-bold">Package Management</h3>
                        <p class="text-gray-400 mt-2">Your one-stop solution for travel packages</p>
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="text-gray-400 hover:text-white transition">Terms</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Privacy</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Contact</a>
                    </div>
                </div>
                <div class="border-t border-gray-700 mt-6 pt-6 text-center text-gray-400">
                    <p>&copy; {{ new Date().getFullYear() }} Package Management. All rights reserved.</p>
                </div>
            </div>
        </footer>
        
        <!-- Toast Notifications -->
        <div v-if="flash && (flash.success || flash.error || flash.warning)" 
             class="fixed top-4 right-4 z-50">
            <div v-if="flash.success" 
                 class="toast toast-success slide-in">
                <div class="flex items-center">
                    <FontAwesomeIcon icon="check" class="text-green-500 mr-2" />
                    <span class="font-medium">{{ flash.success }}</span>
                </div>
            </div>
            <div v-if="flash.error" 
                 class="toast toast-error slide-in">
                <div class="flex items-center">
                    <FontAwesomeIcon icon="exclamation-triangle" class="text-red-500 mr-2" />
                    <span class="font-medium">{{ flash.error }}</span>
                </div>
            </div>
            <div v-if="flash.warning" 
                 class="toast toast-warning slide-in">
                <div class="flex items-center">
                    <FontAwesomeIcon icon="exclamation-triangle" class="text-yellow-500 mr-2" />
                    <span class="font-medium">{{ flash.warning }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Navbar from '@/Components/Shared/Navbar.vue';
import { usePage } from '@inertiajs/vue3';
import { watch, computed } from 'vue';

const page = usePage();

// Safely access flash
const flash = computed(() => page.props.flash || {});

// Auto-hide flash messages after 5 seconds
watch(flash, (f) => {
    if (f.success || f.error || f.warning) {
        setTimeout(() => {
            f.success = null;
            f.error = null;
            f.warning = null;
        }, 5000);
    }
}, { deep: true });
</script>
