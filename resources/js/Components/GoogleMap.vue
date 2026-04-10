<script setup>
import { computed } from 'vue';

const props = defineProps({
    latitude: {
        type: Number,
        required: false, // Could be null if not loaded yet
    },
    longitude: {
        type: Number,
        required: false,
    },
    zoom: {
        type: Number,
        default: 16
    }
});

const mapUrl = computed(() => {
    if (!props.latitude || !props.longitude) return '';
    return `https://maps.google.com/maps?q=${props.latitude},${props.longitude}&t=&z=${props.zoom}&ie=UTF8&iwloc=&output=embed`;
});
</script>

<template>
    <div class="w-full h-full rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 relative min-h-[300px]">
        <iframe 
            v-if="latitude && longitude"
            :src="mapUrl" 
            class="absolute inset-0 w-full h-full border-0"
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
        <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 font-medium">
            <svg class="w-10 h-10 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <span>รอพิกัด GPS...</span>
        </div>
    </div>
</template>
