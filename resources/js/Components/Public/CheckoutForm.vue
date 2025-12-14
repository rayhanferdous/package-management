<template>
    <div class="space-y-6">
        <!-- Package Summary -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Package Summary</h3>
            <div class="flex items-start space-x-4">
                <img :src="imageUrl"
                     :alt="package.name"
                     class="w-24 h-24 object-cover rounded-lg">
                <div class="flex-1">
                    <h4 class="font-bold text-gray-800">{{ package.name }}</h4>
                    <p class="text-gray-600 text-sm mt-1 line-clamp-2">{{ package.description }}</p>
                    <div class="mt-2">
                        <span class="text-2xl font-bold text-blue-600">${{ currentPrice.price.toFixed(2) }}</span>
                        <span class="text-sm text-gray-500 ml-2">({{ formatPriceType(currentPrice.price_type) }})</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Purchase Details -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Purchase Details</h3>
            <div class="space-y-4">
                <!-- Date Selection -->
                <div>
                    <label class="form-label">Purchase Date</label>
                    <input type="date"
                           v-model="form.purchase_date"
                           :min="today"
                           class="form-input"
                           :class="{ 'border-red-500': errors.purchase_date }">
                    <p v-if="errors.purchase_date" class="mt-1 text-sm text-red-600">{{ errors.purchase_date }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Select the date you want to book the package for
                    </p>
                </div>
                
                <!-- Special Notes -->
                <div>
                    <label class="form-label">Special Notes (Optional)</label>
                    <textarea v-model="form.notes"
                              rows="3"
                              class="form-input"
                              :class="{ 'border-red-500': errors.notes }"
                              placeholder="Any special requests or requirements..."></textarea>
                    <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
                </div>
                
                <!-- User Information -->
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2">Your Information</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Name</div>
                                <div class="font-medium">{{ $page.props.auth.user.name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Email</div>
                                <div class="font-medium">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Price Breakdown -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Price Breakdown</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Package Price</span>
                    <span class="font-semibold">${{ currentPrice.price.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tax</span>
                    <span class="font-semibold">$0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Processing Fee</span>
                    <span class="font-semibold">$0.00</span>
                </div>
                <div class="border-t pt-3">
                    <div class="flex justify-between text-lg">
                        <span class="font-bold text-gray-800">Total Amount</span>
                        <span class="font-bold text-blue-600">${{ currentPrice.price.toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Button -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Proceed to Payment</h3>
            <p class="text-gray-600 mb-4">
                You will be redirected to Stripe to complete your payment securely.
            </p>
            
            <button @click="processPayment"
                    :disabled="processing"
                    class="w-full btn-primary py-3 text-lg flex items-center justify-center">
                <FontAwesomeIcon v-if="processing" icon="spinner" class="animate-spin mr-2" />
                {{ processing ? 'Processing...' : `Pay $${currentPrice.price.toFixed(2)}` }}
            </button>
            
            <p class="text-center text-gray-500 text-sm mt-4">
                <FontAwesomeIcon icon="lock" class="mr-1" />
                Secure payment powered by Stripe
            </p>
            
            <!-- Test Card Info -->
            <div v-if="isTestMode" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h4 class="font-semibold text-yellow-800 mb-2">Test Mode</h4>
                <p class="text-sm text-yellow-700 mb-2">
                    Use the following test card for payment:
                </p>
                <div class="text-sm">
                    <div class="font-mono bg-white p-2 rounded border">4242 4242 4242 4242</div>
                    <div class="text-xs text-yellow-600 mt-2">
                        Any future date for expiry, any 3 digits for CVC
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    package: {
        type: Object,
        required: true
    },
    currentPrice: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const today = new Date().toISOString().split('T')[0];
const processing = ref(false);
const isTestMode = import.meta.env.VITE_APP_ENV === 'local' || import.meta.env.VITE_APP_ENV === 'testing';

const form = ref({
    purchase_date: today,
    notes: ''
});

const imageUrl = computed(() => {
    if (props.package.images?.length > 0) {
        const image = props.package.images[0];
        return image.image_path.startsWith('http') ? image.image_path : `/storage/${image.image_path}`;
    }
    return '/images/placeholder.jpg';
});

const formatPriceType = (type) => {
    const types = {
        weekday: 'Weekday Rate',
        weekend: 'Weekend Rate',
        date_range: 'Special Rate'
    };
    return types[type] || type;
};

const processPayment = () => {
    processing.value = true;
    
    router.post(route('purchase.store', props.package), form.value, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>