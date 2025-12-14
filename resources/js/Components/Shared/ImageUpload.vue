<template>
    <div>
        <!-- Image Preview -->
        <div v-if="previews.length > 0" class="mb-6">
            <label class="form-label">Current Images</label>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div v-for="(preview, index) in previews" 
                     :key="index" 
                     class="relative group">
                    <img :src="preview.url" 
                         :alt="`Image ${index + 1}`"
                         class="w-full h-32 object-cover rounded-lg">
                    
                    <!-- Remove Button -->
                    <button v-if="removable"
                            @click="removeImage(index)"
                            type="button"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <FontAwesomeIcon icon="times" class="w-3 h-3" />
                    </button>
                    
                    <!-- Set as Main -->
                    <button v-if="previews.length > 1"
                            @click="setMainImage(index)"
                            type="button"
                            class="absolute top-2 left-2 bg-blue-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <FontAwesomeIcon icon="star" :class="{'text-yellow-400': preview.isMain}" class="w-3 h-3" />
                    </button>
                    
                    <!-- Main Badge -->
                    <span v-if="preview.isMain" 
                          class="absolute bottom-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                        Main
                    </span>
                </div>
            </div>
        </div>

        <!-- Upload Area -->
        <div v-if="maxImages === null || modelValue.length < maxImages" 
             class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-red-500 transition-colors">
            <input type="file" 
                   ref="fileInput"
                   multiple
                   :accept="accept"
                   @change="handleFileSelect"
                   class="hidden">
            
            <div class="space-y-3">
                <FontAwesomeIcon icon="upload" class="text-gray-400 text-3xl mx-auto" />
                
                <div>
                    <button type="button"
                            @click="$refs.fileInput.click()"
                            class="btn-primary">
                        Choose Images
                    </button>
                    <p class="text-sm text-gray-500 mt-2">
                        Click to upload or drag and drop
                    </p>
                </div>
                
                <div class="text-xs text-gray-400">
                    <p>Supported formats: {{ accept }}</p>
                    <p>Max file size: {{ maxSize }}MB</p>
                    <p v-if="maxImages">Max images: {{ maxImages }}</p>
                </div>
            </div>
            
            <!-- Drop Zone -->
            <div class="absolute inset-0"
                 @dragover.prevent="dragOver = true"
                 @dragleave="dragOver = false"
                 @drop.prevent="handleFileDrop">
                <div v-if="dragOver" 
                     class="absolute inset-0 bg-blue-50 border-2 border-red-500 rounded-lg flex items-center justify-center">
                    <span class="text-blue-600 font-medium">Drop images here</span>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        <div v-if="errors.length > 0" class="mt-4 space-y-2">
            <div v-for="(error, index) in errors" 
                 :key="index"
                 class="text-red-600 text-sm flex items-center">
                <FontAwesomeIcon icon="exclamation-triangle" class="mr-2" />
                {{ error }}
            </div>
        </div>

        <!-- Loading Overlay -->
        <div v-if="uploading" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 flex flex-col items-center">
                <div class="loading-spinner w-12 h-12 mb-4"></div>
                <p class="text-gray-700">Uploading images...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    existingImages: {
        type: Array,
        default: () => []
    },
    maxImages: {
        type: Number,
        default: null
    },
    maxSize: {
        type: Number,
        default: 2
    },
    accept: {
        type: String,
        default: 'image/*'
    },
    removable: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'images-removed']);

const fileInput = ref(null);
const dragOver = ref(false);
const uploading = ref(false);
const errors = ref([]);

// Process existing images
const existingPreviews = computed(() => {
    return props.existingImages.map((image, index) => ({
        url: image.image_path.startsWith('http') ? image.image_path : `/storage/${image.image_path}`,
        file: null,
        isMain: index === 0,
        isExisting: true,
        id: image.id
    }));
});

// Combine existing and new images
const previews = computed(() => {
    return [...existingPreviews.value, ...props.modelValue.map(img => ({
        url: img.url,
        file: img.file,
        isMain: img.isMain || false,
        isExisting: false
    }))];
});

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    processFiles(files);
    event.target.value = '';
};

const handleFileDrop = (event) => {
    dragOver.value = false;
    const files = Array.from(event.dataTransfer.files);
    processFiles(files);
};

const processFiles = (files) => {
    errors.value = [];
    
    // Check max images
    if (props.maxImages && (previews.value.length + files.length) > props.maxImages) {
        errors.value.push(`Cannot upload more than ${props.maxImages} images`);
        return;
    }
    
    const validFiles = [];
    
    files.forEach(file => {
        // Check file type
        if (!file.type.startsWith('image/')) {
            errors.value.push(`${file.name} is not an image file`);
            return;
        }
        
        // Check file size
        if (file.size > props.maxSize * 1024 * 1024) {
            errors.value.push(`${file.name} exceeds ${props.maxSize}MB limit`);
            return;
        }
        
        validFiles.push(file);
    });
    
    if (validFiles.length === 0) return;
    
    // Create previews and add to model
    validFiles.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const newImage = {
                url: e.target.result,
                file: file,
                isMain: previews.value.length === 0, // First image is main
                isExisting: false
            };
            
            const updatedValue = [...props.modelValue, newImage];
            emit('update:modelValue', updatedValue);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    const image = previews.value[index];
    
    if (image.isExisting) {
        // Emit event for parent to handle existing image removal
        emit('images-removed', [image.id]);
    } else {
        // Remove from new images
        const newImages = props.modelValue.filter(img => img.url !== image.url);
        emit('update:modelValue', newImages);
    }
};

const setMainImage = (index) => {
    const updatedPreviews = previews.value.map((preview, i) => ({
        ...preview,
        isMain: i === index
    }));
    
    // Update model value for new images
    const updatedModelValue = props.modelValue.map(img => ({
        ...img,
        isMain: img.url === previews.value[index].url
    }));
    
    emit('update:modelValue', updatedModelValue);
};

// Clear errors after 5 seconds
watch(errors, (newErrors) => {
    if (newErrors.length > 0) {
        setTimeout(() => {
            errors.value = [];
        }, 5000);
    }
});
</script>

<style scoped>
    @reference 'tailwindcss';
.drop-zone-active {
    @apply border-red-500 bg-blue-50;
}
</style>