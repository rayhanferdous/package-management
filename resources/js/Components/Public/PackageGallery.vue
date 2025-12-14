<template>
    <div class="space-y-4">
        <!-- Main Image -->
        <div class="relative">
            <img :src="currentImage"
                 :alt="`${package.name} image`"
                 class="w-full h-96 object-cover rounded-lg shadow-lg">
            
            <!-- Navigation -->
            <button v-if="images.length > 1"
                    @click="prevImage"
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 w-10 h-10 rounded-full flex items-center justify-center shadow-lg">
                <FontAwesomeIcon icon="chevron-left" />
            </button>
            <button v-if="images.length > 1"
                    @click="nextImage"
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 w-10 h-10 rounded-full flex items-center justify-center shadow-lg">
                <FontAwesomeIcon icon="chevron-right" />
            </button>
            
            <!-- Image Counter -->
            <div v-if="images.length > 1"
                 class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                {{ currentIndex + 1 }} / {{ images.length }}
            </div>
        </div>
        
        <!-- Thumbnails -->
        <div v-if="images.length > 1" class="flex space-x-2 overflow-x-auto py-2">
            <button v-for="(image, index) in images"
                    :key="index"
                    @click="setImage(index)"
                    :class="[
                        'flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition',
                        currentIndex === index ? 'border-red-500' : 'border-transparent'
                    ]">
                <img :src="image.url"
                     :alt="`Thumbnail ${index + 1}`"
                     class="w-full h-full object-cover">
            </button>
        </div>
        
        <!-- No Images -->
        <div v-if="images.length === 0" 
             class="w-full h-96 bg-gray-100 rounded-lg flex flex-col items-center justify-center">
            <FontAwesomeIcon icon="image" class="text-gray-400 text-5xl mb-4" />
            <p class="text-gray-500">No images available</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    package: {
        type: Object,
        required: true
    }
});

const currentIndex = ref(0);

const images = computed(() => {
    if (!props.package.images) return [];
    
    return props.package.images.map(img => ({
        url: img.image_path.startsWith('http') ? img.image_path : `/storage/${img.image_path}`,
        id: img.id
    }));
});

const currentImage = computed(() => {
    return images.value[currentIndex.value]?.url || '';
});

const nextImage = () => {
    currentIndex.value = (currentIndex.value + 1) % images.value.length;
};

const prevImage = () => {
    currentIndex.value = (currentIndex.value - 1 + images.value.length) % images.value.length;
};

const setImage = (index) => {
    currentIndex.value = index;
};
</script>