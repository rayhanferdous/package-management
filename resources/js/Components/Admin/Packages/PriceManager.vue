<template>
    <div class="space-y-6">
        <!-- Weekday Pricing -->
        <div class="border rounded-lg p-4 bg-gray-50">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-800">Weekday Pricing</h4>
                <span class="text-sm text-gray-500">Applies to selected weekdays</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Price ($)</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           v-model="form.weekday.price"
                           class="form-input"
                           :class="{ 'border-red-500': errors.weekday_price }"
                           placeholder="0.00">
                    <p v-if="errors.weekday_price" class="mt-1 text-sm text-red-600">{{ errors.weekday_price }}</p>
                </div>
                
                <div>
                    <label class="form-label">Weekdays</label>
                    <div class="grid grid-cols-7 gap-2 mt-2">
                        <label v-for="day in daysOfWeek" 
                               :key="day.value"
                               class="flex items-center">
                            <input type="checkbox"
                                   :value="day.value"
                                   v-model="form.weekday.days"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">{{ day.label }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Weekend Pricing -->
        <div class="border rounded-lg p-4 bg-gray-50">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-800">Weekend Pricing</h4>
                <span class="text-sm text-gray-500">Applies to selected weekend days</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Price ($)</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           v-model="form.weekend.price"
                           class="form-input"
                           :class="{ 'border-red-500': errors.weekend_price }"
                           placeholder="0.00">
                    <p v-if="errors.weekend_price" class="mt-1 text-sm text-red-600">{{ errors.weekend_price }}</p>
                </div>
                
                <div>
                    <label class="form-label">Weekend Days</label>
                    <div class="grid grid-cols-7 gap-2 mt-2">
                        <label v-for="day in daysOfWeek" 
                               :key="day.value"
                               class="flex items-center">
                            <input type="checkbox"
                                   :value="day.value"
                                   v-model="form.weekend.days"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">{{ day.label }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Special Pricing -->
        <div class="border rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-800">Special Date Range Pricing</h4>
                <button type="button"
                        @click="addSpecialPrice"
                        class="btn-primary text-sm flex items-center">
                    <FontAwesomeIcon icon="plus" class="mr-1" />
                    Add Special Price
                </button>
            </div>
            
            <p class="text-sm text-gray-600 mb-4">
                Add special pricing for specific date ranges (e.g., holidays, promotions)
            </p>
            
            <!-- Special Prices List -->
            <div v-if="form.special_prices.length > 0" class="space-y-4">
                <div v-for="(special, index) in form.special_prices" 
                     :key="index"
                     class="border rounded-lg p-4 bg-blue-50">
                    <div class="flex justify-between items-start mb-4">
                        <h5 class="font-medium text-gray-800">Special Price #{{ index + 1 }}</h5>
                        <button type="button"
                                @click="removeSpecialPrice(index)"
                                class="text-red-600 hover:text-red-800">
                            <FontAwesomeIcon icon="times" />
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Price ($)</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   v-model="special.price"
                                   class="form-input"
                                   :class="{ 'border-red-500': errors[`special_prices.${index}.price`] }"
                                   placeholder="0.00">
                        </div>
                        
                        <div>
                            <label class="form-label">Start Date</label>
                            <input type="date"
                                   v-model="special.start_date"
                                   class="form-input"
                                   :class="{ 'border-red-500': errors[`special_prices.${index}.start_date`] }">
                        </div>
                        
                        <div>
                            <label class="form-label">End Date</label>
                            <input type="date"
                                   v-model="special.end_date"
                                   :min="special.start_date"
                                   class="form-input"
                                   :class="{ 'border-red-500': errors[`special_prices.${index}.end_date`] }">
                        </div>
                    </div>
                    
                    <!-- Error Messages -->
                    <div v-if="errors[`special_prices.${index}.price`] || 
                              errors[`special_prices.${index}.start_date`] || 
                              errors[`special_prices.${index}.end_date`]"
                         class="mt-2 space-y-1">
                        <p v-if="errors[`special_prices.${index}.price`]" 
                           class="text-sm text-red-600">
                            {{ errors[`special_prices.${index}.price`] }}
                        </p>
                        <p v-if="errors[`special_prices.${index}.start_date`]" 
                           class="text-sm text-red-600">
                            {{ errors[`special_prices.${index}.start_date`] }}
                        </p>
                        <p v-if="errors[`special_prices.${index}.end_date`]" 
                           class="text-sm text-red-600">
                            {{ errors[`special_prices.${index}.end_date`] }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-else class="text-center py-6 border-2 border-dashed border-gray-300 rounded-lg">
                <FontAwesomeIcon icon="calendar" class="text-gray-400 text-3xl mb-2" />
                <p class="text-gray-500">No special prices configured</p>
                <p class="text-gray-400 text-sm mt-1">Add special pricing for holidays or promotions</p>
            </div>
            
            <div v-if="errors.special_prices" class="mt-2">
                <p class="text-sm text-red-600">{{ errors.special_prices }}</p>
            </div>
        </div>
        
        <!-- Pricing Preview -->
        <div class="border rounded-lg p-4 bg-green-50">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Pricing Preview</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-white rounded-lg">
                    <div class="text-sm text-gray-500 mb-1">Weekday Price</div>
                    <div class="text-2xl font-bold text-gray-800">${{ form.weekday.price || '0.00' }}</div>
                    <div class="text-xs text-gray-400 mt-1">
                        Applies to: {{ formatDays(form.weekday.days) }}
                    </div>
                </div>
                
                <div class="text-center p-4 bg-white rounded-lg">
                    <div class="text-sm text-gray-500 mb-1">Weekend Price</div>
                    <div class="text-2xl font-bold text-gray-800">${{ form.weekend.price || '0.00' }}</div>
                    <div class="text-xs text-gray-400 mt-1">
                        Applies to: {{ formatDays(form.weekend.days) }}
                    </div>
                </div>
                
                <div class="text-center p-4 bg-white rounded-lg">
                    <div class="text-sm text-gray-500 mb-1">Special Prices</div>
                    <div class="text-2xl font-bold text-gray-800">{{ form.special_prices.length }}</div>
                    <div class="text-xs text-gray-400 mt-1">
                        Active date ranges
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:modelValue']);

const daysOfWeek = [
    { value: 0, label: 'Sun' },
    { value: 1, label: 'Mon' },
    { value: 2, label: 'Tue' },
    { value: 3, label: 'Wed' },
    { value: 4, label: 'Thu' },
    { value: 5, label: 'Fri' },
    { value: 6, label: 'Sat' }
];

const form = ref({ ...props.modelValue });

const addSpecialPrice = () => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 7);
    
    form.value.special_prices.push({
        price: 0,
        start_date: tomorrow.toISOString().split('T')[0],
        end_date: nextWeek.toISOString().split('T')[0]
    });
    
    emit('update:modelValue', form.value);
};

const removeSpecialPrice = (index) => {
    form.value.special_prices.splice(index, 1);
    emit('update:modelValue', form.value);
};

const formatDays = (days) => {
    if (!days || days.length === 0) return 'None';
    
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    return days.map(day => dayNames[day]).join(', ');
};

// Emit updates when form changes
watch(form, (newValue) => {
    emit('update:modelValue', newValue);
}, { deep: true });

// Update form when modelValue changes
watch(() => props.modelValue, (newValue) => {
    form.value = { ...newValue };
}, { deep: true });
</script>