<template>
    <nav class="bg-white shadow-lg fixed w-full top-0 z-40">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link :href="route('packages.index')" class="flex items-center">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <FontAwesomeIcon icon="box" class="text-white text-xl" />
                        </div>
                        <span class="text-xl font-bold text-gray-800">PackageManager</span>
                    </Link>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <Link :href="route('packages.index')" 
                          class="text-gray-700 hover:text-blue-600 transition"
                          :class="{ 'text-blue-600 font-semibold': $page.url.startsWith('/packages') }">
                        Packages
                    </Link>
                    
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('purchases.index')" 
                              class="text-gray-700 hover:text-blue-600 transition"
                              :class="{ 'text-blue-600 font-semibold': $page.url.startsWith('/purchases') }">
                            My Purchases
                        </Link>
                        
                        <!-- Admin Menu -->
                        <template v-if="$page.props.auth.user.roles?.some(r => ['Admin', 'Super Admin'].includes(r.name))">
                            <div class="relative group">
                                <button class="text-gray-700 hover:text-blue-600 transition flex items-center">
                                    Admin
                                    <FontAwesomeIcon icon="chevron-down" class="ml-1 text-xs" />
                                </button>
                                <div class="absolute hidden group-hover:block bg-white shadow-lg rounded-lg mt-2 py-2 w-48 z-50">
                                    <Link :href="route('admin.dashboard')" 
                                          class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        Dashboard
                                    </Link>
                                    <Link :href="route('admin.packages.index')" 
                                          class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        Packages
                                    </Link>
                                    <Link :href="route('admin.purchases.index')" 
                                          class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        Purchases
                                    </Link>
                                    
                                    <!-- Super Admin Menu -->
                                    <template v-if="$page.props.auth.user.roles?.some(r => r.name === 'Super Admin')">
                                        <div class="border-t my-1"></div>
                                        <Link :href="route('super-admin.users.index')" 
                                              class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                            Users
                                        </Link>
                                        <Link :href="route('super-admin.settings.index')" 
                                              class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                            Settings
                                        </Link>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </template>
                </div>

                <!-- Right Side -->
                <div class="flex items-center space-x-4">
                    <!-- Search (Desktop) -->
                    <div class="hidden md:block relative">
                        <input type="text" 
                               v-model="searchQuery"
                               @keyup.enter="search"
                               placeholder="Search packages..."
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-red-500 focus:outline-none">
                        <FontAwesomeIcon icon="search" class="absolute left-3 top-3 text-gray-400" />
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <template v-if="$page.props.auth.user">
                            <div class="relative group">
                                <button class="flex items-center space-x-2 focus:outline-none">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <FontAwesomeIcon icon="user" class="text-blue-600" />
                                    </div>
                                    <span class="hidden md:inline text-gray-700">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <FontAwesomeIcon icon="chevron-down" class="text-gray-500 text-xs" />
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block z-50">
                                    <Link :href="route('profile.index')" 
                                          class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <FontAwesomeIcon icon="user" class="mr-2" /> Profile
                                    </Link>
                                    <Link :href="route('profile.index')" 
                                          class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <FontAwesomeIcon icon="history" class="mr-2" /> Purchase History
                                    </Link>
                                    <div class="border-t my-1"></div>
                                    <Link :href="route('logout')" method="post" as="button"
                                          class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                                        <FontAwesomeIcon icon="sign-out-alt" class="mr-2" /> Logout
                                    </Link>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" 
                                  class="text-gray-700 hover:text-blue-600 transition">
                                Login
                            </Link>
                            <Link :href="route('register')" 
                                  class="btn-primary">
                                Register
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="md:hidden text-gray-700 focus:outline-none">
                        <FontAwesomeIcon :icon="mobileMenuOpen ? 'times' : 'bars'" class="text-2xl" />
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden border-t py-4 space-y-4">
                <Link :href="route('packages.index')" 
                      @click="mobileMenuOpen = false"
                      class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                    Packages
                </Link>
                
                <div v-if="$page.props.auth.user" class="space-y-2">
                    <Link :href="route('purchases.index')" 
                          @click="mobileMenuOpen = false"
                          class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                        My Purchases
                    </Link>
                    
                    <!-- Admin Mobile Menu -->
                    <template v-if="$page.props.auth.user.roles?.some(r => ['Admin', 'Super Admin'].includes(r.name))">
                        <div class="px-4 py-2 font-semibold text-gray-500">Admin</div>
                        <Link :href="route('admin.dashboard')" 
                              @click="mobileMenuOpen = false"
                              class="block px-6 py-2 text-gray-700 hover:bg-gray-100 rounded">
                            Dashboard
                        </Link>
                        <Link :href="route('admin.packages.index')" 
                              @click="mobileMenuOpen = false"
                              class="block px-6 py-2 text-gray-700 hover:bg-gray-100 rounded">
                            Packages
                        </Link>
                        <Link :href="route('admin.purchases.index')" 
                              @click="mobileMenuOpen = false"
                              class="block px-6 py-2 text-gray-700 hover:bg-gray-100 rounded">
                            Purchases
                        </Link>
                        
                        <template v-if="$page.props.auth.user.roles?.some(r => r.name === 'Super Admin')">
                            <div class="px-6 py-2 font-semibold text-gray-500">Super Admin</div>
                            <Link :href="route('super-admin.users.index')" 
                                  @click="mobileMenuOpen = false"
                                  class="block px-8 py-2 text-gray-700 hover:bg-gray-100 rounded">
                                Users
                            </Link>
                            <Link :href="route('super-admin.settings.index')" 
                                  @click="mobileMenuOpen = false"
                                  class="block px-8 py-2 text-gray-700 hover:bg-gray-100 rounded">
                                Settings
                            </Link>
                        </template>
                    </template>
                    
                    <div class="border-t pt-2">
                        <Link :href="route('profile.index')" 
                              @click="mobileMenuOpen = false"
                              class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                            Profile
                        </Link>
                        <Link :href="route('logout')" method="post" as="button"
                              @click="mobileMenuOpen = false"
                              class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 rounded">
                            Logout
                        </Link>
                    </div>
                </div>
                <template v-else>
                    <div class="px-4 space-y-2">
                        <Link :href="route('login')" 
                              @click="mobileMenuOpen = false"
                              class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                            Login
                        </Link>
                        <Link :href="route('register')" 
                              @click="mobileMenuOpen = false"
                              class="btn-primary block text-center">
                            Register
                        </Link>
                    </div>
                </template>
                
                <!-- Mobile Search -->
                <div class="px-4">
                    <div class="relative">
                        <input type="text" 
                               v-model="searchQuery"
                               @keyup.enter="search"
                               placeholder="Search packages..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-red-500 focus:outline-none">
                        <FontAwesomeIcon icon="search" class="absolute left-3 top-3 text-gray-400" />
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {route} from 'ziggy-js';
const mobileMenuOpen = ref(false);
const searchQuery = ref('');

const search = () => {
    if (searchQuery.value.trim()) {
        router.visit(route('packages.index', { search: searchQuery.value }));
        mobileMenuOpen.value = false;
    }
};
</script>